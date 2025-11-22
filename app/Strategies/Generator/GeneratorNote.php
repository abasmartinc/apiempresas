<?php
namespace App\Strategies\Generator;
use App\Strategies\NoteStrategy;

class GeneratorNote implements NoteStrategy
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function validateData(): bool
    {
        if (empty($this->data->note_type)) {
            throw new \InvalidArgumentException("The 'note_type' field is required.");
        }
        return true;
    }

    public function generateNote(): array
    {
        $this->validateData();

        $result = $this->generateComments();

        return [
            'error' => $result['error'],
            'error_msg' => $result['error_msg'],
            'type' => 'aba',
            'comments' => $result['comments']
        ];
    }

    public function generateComments()
    {
        return [
            'error' => false,
            'error_msg' => '',
            'comments' => "Nota de Generator"
        ];
    }
}
