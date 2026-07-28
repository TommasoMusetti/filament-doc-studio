<?php

namespace TommasoMusetti\DocStudio\Resources;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use TommasoMusetti\DocStudio\DocStudioPlugin;
use TommasoMusetti\DocStudio\DocumentRenderer;
use TommasoMusetti\DocStudio\Models\DocumentTemplate;
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
                ->helperText('The model this template prints, e.g. App\Models\Order.'),

            Builder::make('blocks')
                ->required()
                ->columnSpanFull()
                // The panel decides what may be inserted; the renderer decides
                // what can be printed. Asking the plugin is safe here: a form
                // only ever builds inside a booted panel.
                ->blocks(fn (): array => array_map(
                    fn (string $block) => $block::make(),
                    DocStudioPlugin::get()->getBlocks(),
                )),
        ]);
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
