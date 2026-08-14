<?php

namespace TommasoMusetti\DocStudio\Blocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;
use TommasoMusetti\DocStudio\DocumentRenderer;
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
                // A plain DOM anchor, not an Alpine scope: Filament's Textarea
                // carries its own private x-data (for auto-resize), so $refs
                // from the picker's buttons can never reach across it. The
                // picker instead finds the textarea with a native
                // closest()/querySelector() from this wrapper.
                Group::make([
                    Textarea::make('text')
                        ->label('Text')
                        ->required()
                        ->rows(4),
                    View::make('doc-studio::forms.merge-tag-picker')
                        ->viewData(fn (Get $get): array => ['fields' => static::mergeFields($get)]),
                ])->extraAttributes(['class' => 'fi-doc-studio-merge-tag-group']),
            ]);
    }

    /**
     * The tag is built here, in PHP, and never written literally in the
     * picker's Blade view: Blade compiles {{ }} by scanning the raw template
     * text before it understands PHP string quoting, so a literal
     * "{{field:...}}" inside the view — even inside a PHP string — gets
     * misread as the start of a Blade echo tag.
     *
     * @return array<int, array{label: string, tag: string}>
     */
    protected static function mergeFields(Get $get): array
    {
        // Three levels up: out of this Group, out of the block's own data
        // wrapper, out of the block item, to the Builder's parent form.
        $targetModel = $get('../../../target_model');

        if (! is_string($targetModel) || $targetModel === '') {
            return [];
        }

        $dataSource = app(DocumentRenderer::class)->dataSourceFor($targetModel);

        if ($dataSource === null) {
            return [];
        }

        return collect($dataSource->fields())
            ->map(fn (array $field, string $key): array => [
                'label' => $field['label'],
                'tag' => '{{field:' . $key . '}}',
            ])
            ->values()
            ->all();
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
