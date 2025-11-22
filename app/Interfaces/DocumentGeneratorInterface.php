<?php
namespace App\Interfaces;

interface DocumentGeneratorInterface
{
    public function generate(string $filePath, string $content): void;
}
