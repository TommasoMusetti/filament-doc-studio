<?php

namespace TommasoMusetti\DocStudio\Blocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use TommasoMusetti\DocStudio\RenderContext;

class HeadingBlock extends DocumentBlock
{
    public static function make(): Block
    {
        return Block::make('heading')
            ->label('Heading')
            ->icon('heroicon-o-h1')
            ->schema([
                TextInput::make('text')
                    ->label('Text')
                    ->required()
                    ->maxLength(255),
                Select::make('level')
                    ->label('Level')
                    ->options([
                        1 => 'Title',
                        2 => 'Section',
                        3 => 'Subsection',
                    ])
                    ->default(1)
                    ->required(),
            ]);
    }

    public function render(array $data, RenderContext $context): string
    {
        return view('doc-studio::blocks.heading', [
            'text' => $data['text'] ?? '',
            'level' => max(1, min(3, (int) ($data['level'] ?? 1))),
        ])->render();
    }
}
