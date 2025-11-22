<?php

namespace App\Interfaces;

interface StructuredDocumentGeneratorInterface
{
    public function generate(string $filePath, array $structuredData): void;
}
