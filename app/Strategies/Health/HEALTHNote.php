<?php
namespace App\Strategies\Health;

use App\Services\DatabaseService;
use App\Strategies\NoteStrategy;

class HEALTHNote implements NoteStrategy
{
    protected $data;
    protected $databaseService;
    protected $randomGenerator;

    public function __construct($data)
    {
        $this->data = $data;
        $db = \Config\Database::connect($data->group_name ?? 'ocs');
        $this->databaseService = new DatabaseService($db);
        $this->randomGenerator = new RandomGenerator($this->databaseService);
    }

    public function validateData(): bool
    {
        $rules = [
            'note_type' => ['required' => true, 'type' => 'string'],
            'categories' => ['required' => true, 'type' => 'array']
        ];

        foreach ($rules as $field => $rule) {
            $this->validateField($field, $rule);
        }

        return true;
    }

    protected function validateField($field, $rule)
    {
        if (($rule['required'] ?? false) && empty($this->data->$field)) {
            throw new \InvalidArgumentException("The '{$field}' field is required.");
        }

        if (isset($this->data->$field) && isset($rule['type'])) {
            $type = $rule['type'];
            if ($type === 'array' && !is_array($this->data->$field)) {
                throw new \InvalidArgumentException("The '{$field}' field must be an array.");
            }
            if ($type === 'string' && !is_string($this->data->$field)) {
                throw new \InvalidArgumentException("The '{$field}' field must be a string.");
            }
        }
    }

    public function generateNote(): array
    {
        $this->validateData();

        $result = $this->generateComments();

        return [
            'error' => $result['error'],
            'error_msg' => $result['error_msg'],
            'type' => 'health',
            'comments' => $result['comments']
        ];
    }

    public function generateComments()
    {
        $client_name = "((client))";
        $date = "((date))";

        $client_id = $this->data->client_id ?? 1;
        $categories_selected = $this->data->categories ?? [CATEGORY_VISIT_PLACES, CATEGORY_RESTAURANTS, CATEGORY_STORES];
        sort($categories_selected);

        $first_option_category_to_visit = $categories_selected[0];
        $rest_options_categories_to_visit = count($categories_selected) > 1
            ? array_slice($categories_selected, 1)
            : $categories_selected;

        $introText = $this->randomGenerator->using(['table' => 'ng_intros'])->generate();
        $headerOptionsOffered = $this->randomGenerator->using(['table' => 'ng_options_phrases'])->generate();
        $firstOptionOffered = $this->randomGenerator->using([
            'table' => 'ng_category_options',
            'client_id' => $client_id,
            'category' => $first_option_category_to_visit
        ])->generate();

        $second_option_category_to_visit = $rest_options_categories_to_visit[array_rand($rest_options_categories_to_visit)];
        $and_or = $this->randomGenerator->using(['table' => 'ng_actions', 'category' => $second_option_category_to_visit])->generate();
        $secondOptionOffered = $this->randomGenerator->using([
            'table' => 'ng_category_options',
            'client_id' => $client_id,
            'category' => $second_option_category_to_visit,
            'avoid' => ['field' => 'name', 'values' => [$firstOptionOffered]]
        ])->generate();

        $optionsToChoose = [
            ['category' => $first_option_category_to_visit, 'value' => $firstOptionOffered],
            ['category' => $second_option_category_to_visit, 'value' => $secondOptionOffered]
        ];

        $selectedIndex = array_rand($optionsToChoose);
        $selectedOption = $optionsToChoose[$selectedIndex];
        $selectedCategory = $selectedOption['category'];
        $selectedOptionText = $selectedOption['value'];

        $notSelectedIndex = ($selectedIndex === 0) ? 1 : 0;
        $notSelectedCategory = $optionsToChoose[$notSelectedIndex]['category'];

        if (count($categories_selected) == 1)
        {
            $notSelectedOptionText = $optionsToChoose[$notSelectedIndex]['value'];
        }
        else
        {
            if ($notSelectedCategory == CATEGORY_VISIT_PLACES)
            {
                if (count($rest_options_categories_to_visit) > 1) // si hay mas de una categoria(ademas de CATEGORY_VISIT_PLACES) tengo que asegurarme que no se escoja de la categoria que ya se usó
                {
                    $valuesToRemove = [$selectedCategory];
                    $new_rest_options_categories_to_visit = array_filter($rest_options_categories_to_visit, function($value) use ($valuesToRemove) {
                        return !in_array($value, $valuesToRemove);
                    });
                    $notSelectedCategory = $new_rest_options_categories_to_visit[array_rand($new_rest_options_categories_to_visit)];
                }

                $notSelectedOptionText = $this->randomGenerator->using([
                    'table' => 'ng_category_options',
                    'client_id' => $client_id,
                    'category' => $notSelectedCategory,
                    'avoid' => ['field' => 'name', 'values' => [$firstOptionOffered, $secondOptionOffered]]
                ])->generate();

            }
            else
            {
                $notSelectedOptionText = $optionsToChoose[$notSelectedIndex]['value'];
            }
        }
     
        $headerOptionSelected = $this->randomGenerator->using(['table' => 'ng_choices'])->generate();
        $reflectionsText = (new Reflections($this->databaseService))->using(['client_id' => $client_id, 'cats_selected' => [$selectedCategory]])->generate();
        //$this->randomGenerator->using(['table' => 'ng_reflections', 'category' => $selectedCategory])->generate();
        $headerRecomendationSecondOption = $this->randomGenerator->using(['table' => 'ng_recomendations'])->generate();

        $reflectionsText_notSelectedOption = (new Reflections($this->databaseService))->using(['client_id' => $client_id, 'cats_selected' => [$notSelectedCategory], 'avoid' => ['field' => 'name', 'values' => [$reflectionsText]]])->generate();
        /*
        $this->randomGenerator->using([
            'table' => 'ng_reflections',
            'category' => $notSelectedCategory,
            'avoid' => ['field' => 'name', 'values' => [$reflectionsText]]
        ])->generate();
*/
        $goal = (new Goals($this->databaseService))->using(['client_id' => $client_id, 'cats_selected' => [$selectedCategory, $notSelectedCategory]])->generate();

        $tags = (array) ($this->data->tags ?? []);
        $tagReplacer = new TagReplacer($tags);

        // Generar el comentario final aplicando los tags en cada parte
        $final_comment = trim(
            ucfirst($tagReplacer->apply($client_name)) .
            $tagReplacer->apply($date, ' ') .
            $tagReplacer->apply($introText, "\n\n") .
            $tagReplacer->apply($headerOptionsOffered, ', ') .
            $tagReplacer->apply($firstOptionOffered, ' ') .
            $tagReplacer->apply($and_or, ' ') .
            $tagReplacer->apply($secondOptionOffered, ' ') .
            $tagReplacer->apply($headerOptionSelected, '. ') .
            $tagReplacer->apply($selectedOptionText, ' ') .
            $tagReplacer->apply($reflectionsText, '. ') .
            $tagReplacer->apply($headerRecomendationSecondOption, '. ') .
            $tagReplacer->apply($notSelectedOptionText, ' ') .
            $tagReplacer->apply($reflectionsText_notSelectedOption, '. ') .
            $tagReplacer->apply($goal, '. ') .
            '.'
        );

        return ['error' => false, 'error_msg' => '', 'comments' => trim($final_comment)];

    }


}
