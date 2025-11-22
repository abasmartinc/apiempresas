<?php
namespace App\Services\Document;

class ChartGenerator
{
    protected string $chartServiceUrl = 'https://quickchart.io/chart?c=';

    protected function saveChart(string $title, array $labels, array $values, string $type, string $filePath): void
    {
        $chart = [
            'type' => $type,
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => $title,
                    'data' => $values,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.6)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1
                ]]
            ],
            'options' => [
                'responsive' => true,
                'title' => ['display' => true, 'text' => $title],
                'legend' => ['display' => false],
                'scales' => $type === 'bar' || $type === 'horizontalBar'
                    ? ['yAxes' => [['ticks' => ['beginAtZero' => true]]]]
                    : [],
            ]
        ];

        $url = $this->chartServiceUrl . urlencode(json_encode($chart));
        $imageContent = @file_get_contents($url);

        if ($imageContent === false) {
            throw new \RuntimeException("Failed to generate chart image from QuickChart for: {$title}");
        }

        file_put_contents($filePath, $imageContent);
    }

    public function generateAllCharts(int $assessmentId, array $fbaBaselines): array
    {
        $labels = [];
        $frequencies = [];
        $durations = [];
        $intensities = [];
        $latencies = [];

        foreach ($fbaBaselines as $baseline) {
            $labels[] = $baseline['behavior'] ?? 'N/A';
            $frequencies[] = (float)($baseline['frequency_value'] ?? 0);
            $durations[] = (float)($baseline['baseline_duration'] ?? 0);
            $intensities[] = (float)($baseline['baseline_intensity'] ?? 0);
            $latencies[] = (float)($baseline['latency_seconds'] ?? 0);
        }

        $safeLabels = array_map(fn($label) => htmlspecialchars($label), $labels);

        $publicDir = FCPATH . 'generated/';
        if (!is_dir($publicDir)) {
            mkdir($publicDir, 0775, true);
        }

        $chartPaths = [];

        $charts = [
            'frequency' => ['title' => 'Behavior Frequency', 'data' => $frequencies, 'type' => 'bar'],
            'duration' => ['title' => 'Behavior Duration (min)', 'data' => $durations, 'type' => 'line'],
            'intensity' => ['title' => 'Behavior Intensity', 'data' => $intensities, 'type' => 'horizontalBar'],
            'latency' => ['title' => 'Behavior Latency (sec)', 'data' => $latencies, 'type' => 'radar'],
        ];

        foreach ($charts as $key => $chart) {
            $filename = 'chart_' . hash('sha256', $assessmentId . $key . time() . rand()) . '.png';
            $fullPath = $publicDir . $filename;
            $this->saveChart($chart['title'], $safeLabels, $chart['data'], $chart['type'], $fullPath);

            // Devolver URL pública para insertar en PDF
            $chartPaths[$key] = base_url('generated/' . $filename);
        }

        return $chartPaths;
    }
}
