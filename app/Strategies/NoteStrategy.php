<?php
namespace App\Strategies;

interface NoteStrategy
{
    public function validateData(): bool;
    public function generateNote(): array;
    public function generateComments();
}
