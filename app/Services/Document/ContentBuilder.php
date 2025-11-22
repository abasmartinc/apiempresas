<?php
namespace App\Services\Document;

class ContentBuilder
{
    public function buildTextContent(int $assessmentId, array $data, string $scope): string
    {
        $summary = "Assessment ID: {$assessmentId}\nScope: {$scope}\n\n";

        foreach ($data as $section => $values) {
            $summary .= strtoupper($section) . ":\n";
            foreach ((array) $values as $key => $val) {
                $summary .= "- $key: " . (is_array($val) ? json_encode($val) : $val) . "\n";
            }
            $summary .= "\n";
        }

        return $summary;
    }

    public function buildHtmlContent(int $assessmentId, array $data, string $scope, array $charts = []): string
    {
        if ($scope === 'word') {
            $html = "<h1>Assessment Report</h1>";
            $html .= "<p><strong>Assessment ID:</strong> $assessmentId<br>";
            $html .= "<strong>Scope:</strong> $scope</p>";

            foreach ($data as $section => $fields) {
                $sectionTitle = strtoupper(str_replace('_', ' ', $section));
                $html .= "<h2>$sectionTitle</h2>";

                if (is_array($fields)) {
                    $html .= "<table border='1' cellpadding='5' cellspacing='0' width='100%'>";
                    if (array_is_list($fields)) {
                        foreach ($fields as $index => $obj) {
                            $html .= "<tr><td colspan='2'><strong>Item " . ($index + 1) . "</strong></td></tr>";
                            foreach ($obj as $k => $v) {
                                $label = htmlspecialchars(ucfirst(str_replace('_', ' ', $k)));
                                $val = is_array($v) ? implode(', ', array_map('htmlspecialchars', $v)) : htmlspecialchars((string)$v);
                                $html .= "<tr><td>$label</td><td>$val</td></tr>";
                            }
                        }
                    } else {
                        foreach ($fields as $key => $value) {
                            $label = htmlspecialchars(ucfirst(str_replace('_', ' ', $key)));
                            $val = is_array($value) ? implode(', ', array_map('htmlspecialchars', $value)) : htmlspecialchars((string)$value);
                            $html .= "<tr><td>$label</td><td>$val</td></tr>";
                        }
                    }
                    $html .= "</table><br>";
                } else {
                    $html .= "<p>" . htmlspecialchars((string)$fields) . "</p>";
                }
            }

            // Incluir gráficos en Word
            if (!empty($charts)) {
                $html .= "<h2>Behavior Charts</h2>";
                foreach ($charts as $title => $chartUrl) {
                    $label = htmlspecialchars(ucfirst($title));
                    $html .= "<p><strong>$label</strong></p>";
                    $html .= "<img src='$chartUrl' style='max-width:100%; height:auto;'><br><br>";
                }
            }

            return $html;
        }

        // Para PDF, usa la vista Blade
        return view('assessments/template_pdf', [
            'assessmentId' => $assessmentId,
            'scope' => $scope,
            'data' => $data,
            'charts' => $charts,
        ]);
    }
}
