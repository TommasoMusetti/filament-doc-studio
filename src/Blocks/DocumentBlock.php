<?php

namespace TommasoMusetti\DocStudio\Blocks;

use Filament\Forms\Components\Builder\Block;
use TommasoMusetti\DocStudio\RenderContext;

/**
 * A block is the same thing seen from both ends of the walk: an editor field
 * set in Filament, and a piece of print HTML.
 */
abstract class DocumentBlock
{
    /**
     * The editor side: what the user drags into the Builder field.
     */
    abstract public static function make(): Block;

    /**
     * The document side: the HTML dompdf will paginate.
     *
     * @param  array<string, mixed>  $data
     */
    abstract public function render(array $data, RenderContext $context): string;

    /**
     * The key the Builder field stores as a block's "type".
     */
    public static function name(): string
    {
        return static::make()->getName();
    }
}
