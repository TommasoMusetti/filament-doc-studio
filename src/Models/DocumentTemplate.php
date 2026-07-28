<?php

namespace TommasoMusetti\DocStudio\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The columns exist only in the host app's database, so static analysis needs
 * them spelled out here.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property class-string $target_model
 * @property array<int, array<string, mixed>> $blocks
 * @property array<string, mixed>|null $page_settings
 */
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
