<?php

namespace TommasoMusetti\DocStudio\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'target_model',
        'blocks',
        'page_settings',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'page_settings' => 'array',
        ];
    }
}
