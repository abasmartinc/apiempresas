<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\HoldingModel;
use App\Models\CompanyHoldingModel;

class Holding extends BaseController
{
    public function show($slug)
    {
        $holdingModel = new HoldingModel();
        $holding = $holdingModel->getHoldingBySlug($slug);

        if (!$holding) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Grupo empresarial no encontrado.");
        }

        // Clean up 'Grupo' prefix to avoid 'Grupo Grupo' in views
        $holding['name'] = preg_replace('/^grupo\s+/i', '', trim($holding['name']));

        $companyHoldingModel = new CompanyHoldingModel();
        
        // Capture filters
        $filters = [
            'q' => $this->request->getGet('q'),
            'status' => $this->request->getGet('status')
        ];
        
        // 1. Aggregates for Dashboard
        $aggregates = $companyHoldingModel->getHoldingAggregates($holding['id']);
        
        // 2. Top 100 Companies for Table & Graph (with filters)
        $holdingCompanies = $companyHoldingModel->getCompaniesByHolding($holding['id'], 100, $filters);
        
        // 3. Build Graph Data
        $nodes = [];
        $edges = [];
        
        // Central Node (Holding)
        $nodes[] = [
            'id' => 'h_' . $holding['id'],
            'label' => $holding['name'],
            'shape' => 'box',
            'color' => [
                'background' => '#1e293b',
                'border' => '#0f172a'
            ],
            'font' => ['color' => '#ffffff', 'size' => 20, 'face' => 'Inter', 'bold' => true],
            'margin' => 16,
            'shadow' => true
        ];
        
        foreach ($holdingCompanies as $hc) {
            $capitalStr = str_replace([' €', '.', ','], ['', '', '.'], $hc['social_capital'] ?? '0');
            $capital = (float)$capitalStr;
            
            // Logarithmic scale for size
            $nodeSize = 15; // Base slightly larger for the holding view
            if ($capital > 0) {
                $nodeSize = 15 + (log10($capital) * 4);
                if ($nodeSize > 45) $nodeSize = 45; // Max cap
            }

            $estado = esc($hc['status'] ?? 'Desconocido');
            $provincia = esc(ucwords(strtolower($hc['province'] ?? '')));
            
            $nodes[] = [
                'id' => 'c_' . $hc['id'],
                'shape' => 'dot',
                'color' => '#475569', // Slate color for standard nodes
                'title' => "{$hc['name']}\nCIF: {$hc['cif']}\nProvincia: {$provincia}\nEstado: {$estado}",
                'size' => $nodeSize,
                'url' => site_url('informacion-empresa/' . $hc['cif'])
            ];
            
            $edges[] = [
                'from' => 'h_' . $holding['id'],
                'to' => 'c_' . $hc['id'],
                'color' => '#cbd5e1',
                'length' => 180
            ];
        }
        
        $holdingGraphData = [
            'nodes' => $nodes,
            'edges' => $edges
        ];

        $data = [
            'holding' => $holding,
            'aggregates' => $aggregates,
            'holdingCompanies' => $holdingCompanies,
            'holdingGraphData' => json_encode($holdingGraphData),
            'filters' => $filters
        ];

        return view('holding', $data);
    }
}
