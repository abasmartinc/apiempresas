<?php
namespace App\Services;

use App\Strategies\NoteStrategy;

class NoteGeneratorContext
{
    private NoteStrategy $strategy;

    public function setStrategy(NoteStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    public function generateNote(): array
    {
        return $this->strategy->generateNote();
    }
}

