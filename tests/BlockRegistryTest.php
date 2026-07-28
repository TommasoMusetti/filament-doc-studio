<?php

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Filament\Panel;
use TommasoMusetti\DocStudio\Blocks\DocumentBlock;
use TommasoMusetti\DocStudio\Blocks\HeadingBlock;
use TommasoMusetti\DocStudio\DocStudioPlugin;
use TommasoMusetti\DocStudio\DocumentRenderer;
use TommasoMusetti\DocStudio\Models\DocumentTemplate;
use TommasoMusetti\DocStudio\RenderContext;

class StampBlock extends DocumentBlock
{
    public static function make(): Block
    {
        return Block::make('stamp')->schema([TextInput::make('text')]);
    }

    public function render(array $data, RenderContext $context): string
    {
        return '<p class="stamp">' . e($data['text'] ?? '') . '</p>';
    }
}

class NotABlock {}

it('renders a block registered by the host app', function () {
    app(DocumentRenderer::class)->register(StampBlock::class);

    $html = app(DocumentRenderer::class)->html(new DocumentTemplate([
        'blocks' => [['type' => 'stamp', 'data' => ['text' => 'PAID']]],
    ]));

    expect($html)->toContain('<p class="stamp">PAID</p>');
});

it('keeps the built in blocks when the host app adds its own', function () {
    expect(app(DocumentRenderer::class)->register(StampBlock::class)->blocks())
        ->toContain(HeadingBlock::class)
        ->toContain(StampBlock::class);
});

it('refuses to register something that is not a block', function () {
    expect(fn () => app(DocumentRenderer::class)->register(NotABlock::class))
        ->toThrow(InvalidArgumentException::class);
});

it('shares one renderer, so a registration survives', function () {
    app(DocumentRenderer::class)->register(StampBlock::class);

    expect(app(DocumentRenderer::class)->blocks())->toContain(StampBlock::class);
});

it('offers every renderable block to a panel by default', function () {
    expect(DocStudioPlugin::make()->getBlocks())->toBe(app(DocumentRenderer::class)->blocks());
});

it('lets a panel narrow the blocks it offers', function () {
    $plugin = DocStudioPlugin::make()->blocks([HeadingBlock::class]);
    $plugin->register(Panel::make()->id('admin'));

    expect($plugin->getBlocks())->toBe([HeadingBlock::class]);
});

it('rejects an unrenderable block while the panel boots', function () {
    $plugin = DocStudioPlugin::make()->blocks([StampBlock::class]);

    expect(fn () => $plugin->register(Panel::make()->id('admin')))
        ->toThrow(LogicException::class);
});
