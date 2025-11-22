<?php
namespace App\Services\Document;

use App\Interfaces\DocumentGeneratorInterface;
use App\Interfaces\StructuredDocumentGeneratorInterface;

class DocumentGeneratorFactory
{
    public static function create(string $format)
    {
        return match ($format) {
            'pdf' => new PDFGenerator(),
            'word' => new WordGenerator(),
            default => throw new \InvalidArgumentException("Unsupported format: $format"),
        };
    }
}
