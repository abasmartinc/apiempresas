<?php
namespace App\Services\Document;

use App\Interfaces\DocumentGeneratorInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

class PdfGenerator implements DocumentGeneratorInterface
{
    public function generate(string $filePath, string $html): void
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true); // Permite imágenes remotas con http/https

        $dompdf = new Dompdf($options);

        // Carga el contenido HTML
        $dompdf->loadHtml($html);

        // Configura el tamaño del papel y orientación
        $dompdf->setPaper('A4', 'portrait');

        // Renderiza el PDF
        $dompdf->render();

        // Guarda el archivo en el sistema
        file_put_contents($filePath, $dompdf->output());
    }
}
