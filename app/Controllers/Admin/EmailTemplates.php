<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EmailTemplateModel;
use CodeIgniter\API\ResponseTrait;

class EmailTemplates extends BaseController
{
    use ResponseTrait;

    protected $templateModel;

    public function __construct()
    {
        $this->templateModel = new EmailTemplateModel();
    }

    /**
     * List all templates
     */
    public function index()
    {
        $data = [
            'title'     => 'Gestor de Plantillas de Email',
            'templates' => $this->templateModel->findAll(),
        ];

        return view('admin/email_templates/index', $data);
    }

    /**
     * Edit a template
     */
    public function edit($id)
    {
        $template = $this->templateModel->find($id);

        if (!$template) {
            return redirect()->to(site_url('admin/email-templates'))->with('error', 'Plantilla no encontrada.');
        }

        $data = [
            'title'    => 'Editar Plantilla: ' . $template->name,
            'template' => $template,
        ];

        return view('admin/email_templates/edit', $data);
    }

    /**
     * Update a template
     */
    public function update($id)
    {
        $template = $this->templateModel->find($id);

        if (!$template) {
            return redirect()->to(site_url('admin/email-templates'))->with('error', 'Plantilla no encontrada.');
        }

        $rules = [
            'subject' => 'required|min_length[3]',
            'body'    => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'subject'     => $this->request->getPost('subject'),
            'body'        => $this->request->getPost('body'),
            'description' => $this->request->getPost('description'),
        ];

        $this->templateModel->update($id, $updateData);

        return redirect()->to(site_url('admin/email-templates'))->with('message', 'Plantilla actualizada correctamente.');
    }

    /**
     * Preview a template (simple placeholder replacement)
     */
    public function preview($id)
    {
        $template = $this->templateModel->find($id);

        if (!$template) {
            return "Plantilla no encontrada.";
        }

        // Dummy data for preview
        $dummyData = [
            'name'           => 'Juan Pérez',
            'email'          => 'juan@ejemplo.com',
            'company'        => 'Tecnología S.A.',
            'user_id'        => '123',
            'invoice_number' => 'INV-2026-001',
            'amount'         => '49.00',
            'currency'       => 'EUR',
            'plan_name'      => 'Plan Pro',
            'token'          => 'preview-token-xyz',
            'count'          => '15',
            'content'        => 'Este es un contenido de prueba para la plantilla genérica.',
            'button_text'    => 'Botón de Acción',
            'button_url'     => '#'
        ];

        $body = $template->body;
        foreach ($dummyData as $key => $value) {
            $body = str_replace('{' . $key . '}', $value, $body);
        }

        return $body;
    }
}
