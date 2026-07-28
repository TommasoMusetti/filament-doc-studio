<?php

namespace TommasoMusetti\DocStudio\Tests\Fixtures;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use TommasoMusetti\DocStudio\Blocks\DocumentBlock;
use TommasoMusetti\DocStudio\RenderContext;

/**
 * A block a host app would write, used to test the registry.
 */
class StampBlock extends DocumentBlock
{
    public static function make(): Block
    {
        return Block::make('stamp')->schema([
            TextInput::make('text'),
        ]);
    }

    public function render(array $data, RenderContext $context): string
    {
        return '<p class="stamp">' . e($data['text'] ?? '') . '</p>';
    }
}
