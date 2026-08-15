<?php

use TommasoMusetti\DocStudio\Blocks\HeadingBlock;
use TommasoMusetti\DocStudio\Blocks\ParagraphBlock;
use TommasoMusetti\DocStudio\Blocks\TableBlock;
use TommasoMusetti\DocStudio\Contracts\DocumentDataSource;
use TommasoMusetti\DocStudio\DocumentRenderer;
use TommasoMusetti\DocStudio\Models\DocumentTemplate;
use TommasoMusetti\DocStudio\RenderContext;
use TommasoMusetti\DocStudio\Tests\Fixtures\TestDataSource;
use Workbench\App\Models\User;

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

function render(array $blocks, ?RenderContext $context = null): string
{
    return app(DocumentRenderer::class)->html(template($blocks), $context);
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

it('renders a paragraph and escapes what the user typed', function () {
    $html = render([
        ['type' => 'paragraph', 'data' => ['text' => 'Dear <script>alert(1)</script>']],
    ]);

    expect($html)
        ->toContain('<p>Dear &lt;script&gt;alert(1)&lt;/script&gt;</p>')
        ->not->toContain('<script>');
});

it('renders a paragraph whose data is missing', function () {
    expect(render([['type' => 'paragraph', 'data' => []]]))
        ->toContain('<p></p>');
});

it('resolves a merge field against the record in context', function () {
    $context = new RenderContext(
        record: new User(['name' => 'Ann']),
        dataSource: new TestDataSource,
    );

    $html = render([
        ['type' => 'paragraph', 'data' => ['text' => 'Dear {{field:customer_name}},']],
    ], $context);

    expect($html)->toContain('<p>Dear Ann,</p>');
});

it('escapes a resolved merge field value too', function () {
    $context = new RenderContext(
        record: new User(['name' => '<b>Ann</b>']),
        dataSource: new TestDataSource,
    );

    $html = render([
        ['type' => 'paragraph', 'data' => ['text' => '{{field:customer_name}}']],
    ], $context);

    expect($html)
        ->toContain('&lt;b&gt;Ann&lt;/b&gt;')
        ->not->toContain('<b>Ann</b>');
});

it('blanks a merge field the data source no longer whitelists', function () {
    $context = new RenderContext(
        record: new User(['name' => 'Ann']),
        dataSource: new TestDataSource,
    );

    $html = render([
        ['type' => 'paragraph', 'data' => ['text' => 'Ref {{field:removed_key}}.']],
    ], $context);

    expect($html)->toContain('<p>Ref .</p>');
});

it('blanks a merge field when there is no data source at all', function () {
    $html = render([
        ['type' => 'paragraph', 'data' => ['text' => 'Ref {{field:customer_name}}.']],
    ]);

    expect($html)->toContain('<p>Ref .</p>');
});

it('names the paragraph block the same on both ends of the walk', function () {
    expect(ParagraphBlock::name())->toBe('paragraph');
});

it('names the table block the same on both ends of the walk', function () {
    expect(TableBlock::name())->toBe('table');
});

it('renders a table with the selected columns, in data source order', function () {
    $context = new RenderContext(
        record: new User(['name' => 'Ann']),
        dataSource: new TestDataSource,
    );

    $html = render([
        ['type' => 'table', 'data' => ['collection' => 'items', 'columns' => ['qty', 'name']]],
    ], $context);

    expect($html)
        ->toContain('<th')->toContain('>Item<')->toContain('>Qty<')
        ->toContain('>Widget<')->toContain('>2<')
        ->toContain('>Gadget<')->toContain('>1<');

    // 'name' comes before 'qty' in TestDataSource, even though 'columns' asked for qty first.
    expect(strpos($html, '>Item<'))->toBeLessThan(strpos($html, '>Qty<'));
});

it('renders only the columns picked in the editor', function () {
    $context = new RenderContext(
        record: new User(['name' => 'Ann']),
        dataSource: new TestDataSource,
    );

    $html = render([
        ['type' => 'table', 'data' => ['collection' => 'items', 'columns' => ['name']]],
    ], $context);

    expect($html)->toContain('>Item<')->not->toContain('>Qty<');
});

it('escapes a resolved table cell', function () {
    $context = new RenderContext(
        record: new User(['name' => 'Ann']),
        dataSource: new class implements DocumentDataSource
        {
            public static function model(): string
            {
                return User::class;
            }

            public function fields(): array
            {
                return [];
            }

            public function collections(): array
            {
                return [
                    'items' => [
                        'label' => 'Line items',
                        'columns' => ['name' => 'Item'],
                        'resolver' => fn (User $user): array => [['name' => '<b>Widget</b>']],
                    ],
                ];
            }

            public function sample(): User
            {
                return new User(['name' => 'Ann']);
            }
        },
    );

    $html = render([
        ['type' => 'table', 'data' => ['collection' => 'items', 'columns' => ['name']]],
    ], $context);

    expect($html)
        ->toContain('&lt;b&gt;Widget&lt;/b&gt;')
        ->not->toContain('<b>Widget</b>');
});

it('renders an empty table when there is no data source at all', function () {
    $html = render([
        ['type' => 'table', 'data' => ['collection' => 'items', 'columns' => ['name']]],
    ]);

    expect($html)->toContain('<table')->not->toContain('<td');
});

it('shows table headers even with no explicit context, since a template already names its target model', function () {
    app(DocumentRenderer::class)->registerDataSource(TestDataSource::class);

    $html = app(DocumentRenderer::class)->html(new DocumentTemplate([
        'name' => 'Welcome letter',
        'slug' => 'welcome-letter',
        'target_model' => User::class,
        'blocks' => [
            ['type' => 'table', 'data' => ['collection' => 'items', 'columns' => ['name', 'qty']]],
        ],
        'page_settings' => [],
    ]));

    expect($html)
        ->toContain('>Item<')->toContain('>Qty<')
        ->not->toContain('<td');
});

it('renders an empty table for a collection the data source no longer offers', function () {
    $context = new RenderContext(
        record: new User(['name' => 'Ann']),
        dataSource: new TestDataSource,
    );

    $html = render([
        ['type' => 'table', 'data' => ['collection' => 'removed', 'columns' => ['name']]],
    ], $context);

    expect($html)->toContain('<table')->not->toContain('<td');
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
