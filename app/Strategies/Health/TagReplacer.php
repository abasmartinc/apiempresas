<?php 
namespace App\Strategies\Health;

class TagReplacer
{
    protected $tags;

    public function __construct(array $tags = [])
    {
        $this->tags = $tags;
    }

    public function apply($comment, $glue = '')
    {
        if (empty($comment)) return '';

        foreach ($this->tags as $name => $tag) {
            $comment = str_replace($name, $tag, $comment);
        }

        if (trim($glue) == '.')
            $comment = ucfirst($comment);

        $comment = $glue . trim($comment);

        return $comment;
    }
}