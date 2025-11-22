<?php
namespace App\Services\Document;

use App\Interfaces\StructuredDocumentGeneratorInterface;
use PhpOffice\PhpWord\PhpWord;

class WordGenerator implements StructuredDocumentGeneratorInterface
{
    public function generate(string $filePath, array $structuredData): void
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        $assessmentId = $structuredData['assessment_id'] ?? 'N/A';
        $scope = $structuredData['scope'] ?? 'N/A';
        $data = $structuredData['data'] ?? [];
        $charts = $structuredData['charts'] ?? []; // <- Agregado para gráficos

        $section->addTitle("Assessment Report", 1);
        $section->addText("Assessment ID: {$assessmentId}");
        $section->addText("Scope: {$scope}");
        $section->addTextBreak(1);

        foreach ($data as $sectionName => $fields) {
            $section->addTitle(strtoupper(str_replace('_', ' ', $sectionName)), 2);
            if (is_array($fields)) {
                foreach ($fields as $key => $val) {
                    $text = ucfirst(str_replace('_', ' ', $key)) . ': ';
                    $text .= is_array($val) ? implode(', ', $val) : $val;
                    $section->addText($text);
                }
            }
            $section->addTextBreak(1);
        }

        if (!empty($charts)) {
            $section->addTitle('Behavior Charts', 2);
            foreach ($charts as $title => $chartUrl) {
                $section->addText(ucfirst($title) . ':');
                // Convertir URL a archivo temporal para insertar
                $imageContent = @file_get_contents($chartUrl);
                if ($imageContent !== false) {
                    $tmpFile = tempnam(sys_get_temp_dir(), 'chart_') . '.png';
                    file_put_contents($tmpFile, $imageContent);
                    $section->addImage($tmpFile, ['width' => 500, 'height' => 300, 'wrappingStyle' => 'square']);
                    unlink($tmpFile);
                } else {
                    $section->addText('[Image could not be loaded]');
                }
                $section->addTextBreak(1);
            }
        }

        $phpWord->save($filePath, 'Word2007');
    }
}
