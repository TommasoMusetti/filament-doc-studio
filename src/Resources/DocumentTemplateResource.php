<?php

namespace TommasoMusetti\DocStudio\Resources;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Throwable;
use TommasoMusetti\DocStudio\DocStudioPlugin;
use TommasoMusetti\DocStudio\DocumentRenderer;
use TommasoMusetti\DocStudio\Models\DocumentTemplate;
use TommasoMusetti\DocStudio\RenderContext;
use TommasoMusetti\DocStudio\Resources\DocumentTemplateResource\Pages;

class DocumentTemplateResource extends Resource
{
    protected static ?string $model = DocumentTemplate::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(fn (?string $state, Set $set) => $set('slug', Str::slug((string) $state))),

            TextInput::make('slug')
                ->required()
                ->maxLength(255)
                ->rule('alpha_dash')
                ->unique(ignoreRecord: true),

            TextInput::make('target_model')
                ->label('Model')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->helperText('The model this template prints, e.g. App\Models\Order.'),

            Grid::make(2)
                ->columnSpanFull()
                ->components([
                    Builder::make('blocks')
                        ->required()
                        // The panel decides what may be inserted; the
                        // renderer decides what can be printed. Asking the
                        // plugin is safe here: a form only ever builds
                        // inside a booted panel.
                        ->blocks(fn (): array => array_map(
                            fn (string $block) => $block::make(),
                            DocStudioPlugin::get()->getBlocks(),
                        ))
                        // Filament otherwise partially re-renders just this
                        // Builder after add/delete/reorder, skipping the
                        // Preview placeholder next to it — which would then
                        // show a stale block order until some other field's
                        // own update happened to trigger a full re-render.
                        ->partiallyRenderAfterActionsCalled(false),

                    Placeholder::make('preview')
                        ->label('Preview')
                        ->content(fn (Get $get): HtmlString => new HtmlString(static::renderPreview($get))),
                ]),
        ]);
    }

    /**
     * Renders the unsaved form state against the target model's sample()
     * record, live as the user edits. An iframe (not inline HTML) because
     * the print CSS is written dompdf-first and would otherwise leak into
     * the admin panel around it.
     */
    protected static function renderPreview(Get $get): string
    {
        $targetModel = $get('target_model');

        if (! is_string($targetModel) || $targetModel === '') {
            return '<p>Set a model above to preview a sample document.</p>';
        }

        $dataSource = app(DocumentRenderer::class)->dataSourceFor($targetModel);

        if ($dataSource === null) {
            return '<p>No data source registered for this model — the preview needs one to build a sample record.</p>';
        }

        try {
            $html = app(DocumentRenderer::class)->html(
                new DocumentTemplate([
                    'target_model' => $targetModel,
                    'blocks' => $get('blocks') ?? [],
                    'page_settings' => [],
                ]),
                new RenderContext(record: $dataSource->sample(), dataSource: $dataSource),
            );
        } catch (Throwable) {
            // Mid-edit form state can be transiently incomplete (a block
            // added but not yet configured); the preview should stay quiet
            // about it rather than break the page the user is editing.
            return '<p>Preview unavailable for the current, unsaved state.</p>';
        }

        return '<iframe style="width: 100%; height: 32rem; border: 1px solid #d1d5db; background: white;" srcdoc="' . e($html) . '"></iframe>';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('slug')->searchable(),
                TextColumn::make('target_model')->label('Model')->toggleable(),
                TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->recordActions([
                Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn (DocumentTemplate $record) => response()->streamDownload(
                        function () use ($record) {
                            echo app(DocumentRenderer::class)->pdf($record);
                        },
                        "{$record->slug}.pdf",
                    )),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocumentTemplates::route('/'),
            'create' => Pages\CreateDocumentTemplate::route('/create'),
            'edit' => Pages\EditDocumentTemplate::route('/{record}/edit'),
        ];
    }
}
