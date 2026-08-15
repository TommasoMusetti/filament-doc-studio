<?php

namespace TommasoMusetti\DocStudio\Blocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use TommasoMusetti\DocStudio\Contracts\DocumentDataSource;
use TommasoMusetti\DocStudio\DocumentRenderer;
use TommasoMusetti\DocStudio\RenderContext;

class TableBlock extends DocumentBlock
{
    public static function make(): Block
    {
        return Block::make('table')
            ->label('Table')
            ->icon('heroicon-o-table-cells')
            ->schema([
                Select::make('collection')
                    ->label('Collection')
                    ->options(fn (Get $get): array => static::collectionOptions($get))
                    ->live()
                    ->required()
                    ->afterStateUpdated(fn (Set $set) => $set('columns', [])),
                CheckboxList::make('columns')
                    ->label('Columns')
                    ->options(fn (Get $get): array => static::columnOptions($get))
                    ->visible(fn (Get $get): bool => filled($get('collection')))
                    ->required(),
            ]);
    }

    /**
     * @return array<string, string>
     */
    protected static function collectionOptions(Get $get): array
    {
        $dataSource = static::dataSourceFor($get);

        if ($dataSource === null) {
            return [];
        }

        return collect($dataSource->collections())
            ->map(fn (array $collection): string => $collection['label'])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    protected static function columnOptions(Get $get): array
    {
        $dataSource = static::dataSourceFor($get);
        $collectionKey = $get('collection');

        if ($dataSource === null || ! is_string($collectionKey) || $collectionKey === '') {
            return [];
        }

        return $dataSource->collections()[$collectionKey]['columns'] ?? [];
    }

    protected static function dataSourceFor(Get $get): ?DocumentDataSource
    {
        // Three levels up: out of the block's own data wrapper, out of the
        // block item, out of the blocks array, to the Builder's parent form.
        // Same depth as ParagraphBlock's lookup — Group is a layout
        // component and adds no state path segment of its own.
        $targetModel = $get('../../../target_model');

        if (! is_string($targetModel) || $targetModel === '') {
            return null;
        }

        return app(DocumentRenderer::class)->dataSourceFor($targetModel);
    }

    public function render(array $data, RenderContext $context): string
    {
        $collection = $context->resolveCollection((string) ($data['collection'] ?? ''));
        $selected = is_array($data['columns'] ?? null) ? $data['columns'] : [];

        // ponytail: column order follows the data source declaration, not a
        // user-chosen order — add drag-to-reorder if a real template needs it.
        $columns = array_intersect_key($collection['columns'], array_flip($selected));

        return view('doc-studio::blocks.table', [
            'columns' => $columns,
            'rows' => $collection['rows'],
        ])->render();
    }
}
