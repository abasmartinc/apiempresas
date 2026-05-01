<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SeoAutoPostKeywordModel;
use App\Services\SeoAutoPostGeneratorService;
use App\Services\WordPressPublisherService;

class SeoAutoPostsController extends BaseController
{
    protected $keywordModel;
    protected $generatorService;
    protected $publisherService;

    public function __construct()
    {
        $this->keywordModel = new SeoAutoPostKeywordModel();
        $this->generatorService = new SeoAutoPostGeneratorService();
        $this->publisherService = new WordPressPublisherService();
    }

    public function index()
    {
        $data = [
            'title' => 'SEO Auto Posts | APIEmpresas',
            'keywords' => $this->keywordModel->orderBy('created_at', 'DESC')->findAll(),
        ];

        return view('admin/seo_auto_posts/index', $data);
    }

    public function storeKeyword()
    {
        $keyword = $this->request->getPost('keyword');
        $intent  = $this->request->getPost('intent');

        if (empty($keyword)) {
            return redirect()->back()->with('error', 'La keyword es obligatoria.');
        }

        try {
            $this->keywordModel->insert([
                'keyword' => $keyword,
                'intent'  => $intent,
                'status'  => 'pending'
            ]);
            return redirect()->back()->with('message', 'Keyword añadida correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al añadir keyword: ' . $e->getMessage());
        }
    }

    public function generateOne($id)
    {
        $keywordRow = $this->keywordModel->find($id);
        if (!$keywordRow) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Keyword no encontrada.']);
        }

        if ($keywordRow['status'] === 'published') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Este post ya ha sido publicado.']);
        }

        // Marcar como generando
        $this->keywordModel->update($id, ['status' => 'generating', 'error_message' => null]);

        try {
            // 1. Generar con IA
            $generated = $this->generatorService->generate($keywordRow['keyword'], $keywordRow['intent']);
            
            if (!$generated) {
                $this->keywordModel->update($id, [
                    'status' => 'failed',
                    'error_message' => 'Error al generar contenido con OpenAI.'
                ]);
                return $this->response->setJSON(['status' => 'error', 'message' => 'Error al generar contenido con OpenAI.']);
            }

            // Guardar contenido generado localmente
            $this->keywordModel->update($id, [
                'title'             => $generated['title'],
                'meta_description'  => $generated['meta_description'],
                'slug'              => $generated['slug'],
                'generated_content' => $generated['content_html'],
                'status'            => 'generated',
                'generated_at'      => date('Y-m-d H:i:s')
            ]);

            // 2. Publicar en WordPress
            $publishResult = $this->publisherService->publish([
                'title'            => $generated['title'],
                'content'          => $generated['content_html'],
                'slug'             => $generated['slug'],
                'meta_description' => $generated['meta_description'],
                'category_id'      => $keywordRow['category_id'] ?? 29
            ]);

            if ($publishResult && !isset($publishResult['error'])) {
                $this->keywordModel->update($id, [
                    'wordpress_post_id' => $publishResult['wordpress_post_id'],
                    'wordpress_url'     => $publishResult['wordpress_url'],
                    'status'            => 'published',
                    'published_at'      => date('Y-m-d H:i:s')
                ]);
                return $this->response->setJSON(['status' => 'success', 'message' => 'Post generado y publicado correctamente.']);
            } else {
                $errorMsg = $publishResult['message'] ?? 'Error al publicar en WordPress.';
                $this->keywordModel->update($id, [
                    'status' => 'failed',
                    'error_message' => $errorMsg
                ]);
                return $this->response->setJSON(['status' => 'error', 'message' => $errorMsg]);
            }

        } catch (\Exception $e) {
            $this->keywordModel->update($id, [
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            return $this->response->setJSON(['status' => 'error', 'message' => 'Excepción: ' . $e->getMessage()]);
        }
    }

    public function generatePending()
    {
        $pending = $this->keywordModel->where('status', 'pending')->first();
        if (!$pending) {
            return $this->response->setJSON(['status' => 'info', 'message' => 'No hay posts pendientes.']);
        }

        return $this->generateOne($pending['id']);
    }

    public function generateBatch()
    {
        $limit = 3;
        $pending = $this->keywordModel
            ->whereIn('status', ['pending', 'failed'])
            ->limit($limit)
            ->findAll();

        if (empty($pending)) {
            return $this->response->setJSON(['status' => 'info', 'message' => 'No hay posts para procesar.']);
        }

        $results = [
            'total'     => count($pending),
            'published' => 0,
            'failed'    => 0,
            'details'   => []
        ];

        foreach ($pending as $item) {
            $response = $this->generateOne($item['id']);
            $body = json_decode($response->getJSON(), true);
            
            if ($body['status'] === 'success') {
                $results['published']++;
            } else {
                $results['failed']++;
            }
            $results['details'][] = [
                'keyword' => $item['keyword'],
                'status'  => $body['status'],
                'message' => $body['message']
            ];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => "Procesados {$results['total']} posts. Éxito: {$results['published']}, Fallos: {$results['failed']}.",
            'results' => $results
        ]);
    }

    public function retry($id)
    {
        return $this->generateOne($id);
    }
}
