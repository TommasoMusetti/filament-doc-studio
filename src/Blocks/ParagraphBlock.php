<?php

namespace TommasoMusetti\DocStudio\Blocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Textarea;
use TommasoMusetti\DocStudio\RenderContext;

class ParagraphBlock extends DocumentBlock
{
    /**
     * Matches the {{field:key}} placeholders the merge tag picker inserts.
     */
    protected const FIELD_PATTERN = '/\{\{field:([a-zA-Z0-9_]+)\}\}/';

    public static function make(): Block
    {
        return Block::make('paragraph')
            ->label('Paragraph')
            ->icon('heroicon-o-bars-3-bottom-left')
            ->schema([
                Textarea::make('text')
                    ->label('Text')
                    ->required()
                    ->rows(4),
            ]);
    }

    public function render(array $data, RenderContext $context): string
    {
        return view('doc-studio::blocks.paragraph', [
            'text' => $this->interpolate((string) ($data['text'] ?? ''), $context),
        ])->render();
    }

    /**
     * Escapes the raw text first, then swaps in each resolved (and
     * separately escaped) merge field value. Two untrusted sources — what
     * the user typed and what the data source resolves — so both go
     * through e() before reaching the document HTML.
     */
    protected function interpolate(string $text, RenderContext $context): string
    {
        return preg_replace_callback(
            self::FIELD_PATTERN,
            fn (array $match): string => e($context->resolveField($match[1])),
            e($text),
        );
    }
}
