<?php

use TommasoMusetti\DocStudio\Blocks\HeadingBlock;
use TommasoMusetti\DocStudio\DocumentRenderer;
use TommasoMusetti\DocStudio\Models\DocumentTemplate;

/**
 * @param  array<int, array<string, mixed>>  $blocks
 */
function template(array $blocks): DocumentTemplate
{
    return new DocumentTemplate([
        'name' => 'Quote',
        'slug' => 'quote',
        'target_model' => 'App\\Models\\Order',
        'blocks' => $blocks,
        'page_settings' => [],
    ]);
}

function render(array $blocks): string
{
    return app(DocumentRenderer::class)->html(template($blocks));
}

it('renders a heading and escapes what the user typed', function () {
    $html = render([
        ['type' => 'heading', 'data' => ['text' => 'Quote <script>alert(1)</script>', 'level' => 2]],
    ]);

    expect($html)
        ->toContain('<h2>Quote &lt;script&gt;alert(1)&lt;/script&gt;</h2>')
        ->not->toContain('<script>');
});

it('clamps a heading level that never came from the editor', function () {
    expect(render([['type' => 'heading', 'data' => ['text' => 'Hi', 'level' => 99]]]))
        ->toContain('<h3>Hi</h3>');
});

it('renders a block whose data is missing', function () {
    expect(render([['type' => 'heading', 'data' => []]]))
        ->toContain('<h1></h1>');
});

it('refuses a block type it cannot render', function () {
    expect(fn () => render([['type' => 'ghost', 'data' => []]]))
        ->toThrow(InvalidArgumentException::class);
});

it('produces a pdf file', function () {
    $pdf = app(DocumentRenderer::class)->pdf(template([
        ['type' => 'heading', 'data' => ['text' => 'Quote 2026/001', 'level' => 1]],
    ]));

    expect(substr($pdf, 0, 4))->toBe('%PDF');
});

it('names the heading block the same on both ends of the walk', function () {
    expect(HeadingBlock::name())->toBe('heading');
});

it('stores blocks as json and reads them back as an array', function () {
    $saved = DocumentTemplate::create([
        'name' => 'Quote',
        'slug' => 'quote',
        'target_model' => 'App\\Models\\Order',
        'blocks' => [['type' => 'heading', 'data' => ['text' => 'Hi', 'level' => 1]]],
        'page_settings' => ['paper' => 'a4'],
    ]);

    expect($saved->fresh()->blocks[0]['data']['text'])->toBe('Hi');
});
