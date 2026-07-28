<?php

use Filament\Facades\Filament;
use Livewire\Livewire;
use TommasoMusetti\DocStudio\Blocks\HeadingBlock;
use TommasoMusetti\DocStudio\DocStudioPlugin;
use TommasoMusetti\DocStudio\DocumentRenderer;
use TommasoMusetti\DocStudio\Models\DocumentTemplate;
use TommasoMusetti\DocStudio\Resources\DocumentTemplateResource\Pages\CreateDocumentTemplate;
use TommasoMusetti\DocStudio\Resources\DocumentTemplateResource\Pages\EditDocumentTemplate;
use TommasoMusetti\DocStudio\Resources\DocumentTemplateResource\Pages\ListDocumentTemplates;
use TommasoMusetti\DocStudio\Tests\Fixtures\StampBlock;
use Workbench\App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Filament::setCurrentPanel('admin');
    actingAs(User::factory()->create());
});

it('renders the list page', function () {
    Livewire::test(ListDocumentTemplates::class)->assertOk();
});

it('creates a template through the panel form', function () {
    Livewire::test(CreateDocumentTemplate::class)
        ->fillForm([
            'name' => 'Quote',
            'slug' => 'quote',
            'target_model' => 'App\\Models\\Order',
            'blocks' => [
                ['type' => 'heading', 'data' => ['text' => 'Quote 2026/001', 'level' => 1]],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(DocumentTemplate::query()->where('slug', 'quote')->value('blocks'))
        ->toBe([['type' => 'heading', 'data' => ['text' => 'Quote 2026/001', 'level' => 1]]]);
});

it('refuses a template with no blocks', function () {
    Livewire::test(CreateDocumentTemplate::class)
        ->fillForm(['name' => 'Empty', 'slug' => 'empty', 'target_model' => 'App\\Models\\Order'])
        ->call('create')
        ->assertHasFormErrors(['blocks']);
});

it('keeps a block the panel stopped offering', function () {
    app(DocumentRenderer::class)->register(StampBlock::class);

    $template = DocumentTemplate::create([
        'name' => 'Quote',
        'slug' => 'quote',
        'target_model' => 'App\\Models\\Order',
        'blocks' => [
            ['type' => 'heading', 'data' => ['text' => 'Quote', 'level' => 1]],
            ['type' => 'stamp', 'data' => ['text' => 'PAID']],
        ],
    ]);

    // The panel narrows what may be inserted from now on.
    DocStudioPlugin::get()->blocks([HeadingBlock::class]);

    Livewire::test(EditDocumentTemplate::class, ['record' => $template->getRouteKey()])
        ->assertOk()
        ->call('save')
        ->assertHasNoFormErrors();

    // Narrowing the editor limits what comes next, it does not rewrite what
    // was already saved.
    expect(array_column($template->fresh()->blocks, 'type'))->toBe(['heading', 'stamp']);
    expect(substr(app(DocumentRenderer::class)->pdf($template->fresh()), 0, 4))->toBe('%PDF');
});

it('loads an existing template back into the editor', function () {
    $template = DocumentTemplate::create([
        'name' => 'Quote',
        'slug' => 'quote',
        'target_model' => 'App\\Models\\Order',
        'blocks' => [['type' => 'heading', 'data' => ['text' => 'Hi', 'level' => 2]]],
    ]);

    Livewire::test(EditDocumentTemplate::class, ['record' => $template->getRouteKey()])
        ->assertOk()
        ->assertFormSet(['name' => 'Quote', 'slug' => 'quote']);
});
