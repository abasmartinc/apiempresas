<?php

namespace App\Models;

use CodeIgniter\Model;

class SeoAutoPostKeywordModel extends Model
{
    protected $table            = 'seo_auto_post_keywords';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'keyword',
        'intent',
        'slug',
        'title',
        'meta_description',
        'generated_content',
        'wordpress_post_id',
        'wordpress_url',
        'category_id',
        'status',
        'error_message',
        'generated_at',
        'published_at'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'keyword' => 'required|is_unique[seo_auto_post_keywords.keyword,id,{id}]',
        'intent'  => 'required|in_list[informacional,comercial,mixta]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
