<?php

use Filament\Facades\Filament;
use Livewire\Livewire;
use TommasoMusetti\DocStudio\Models\DocumentTemplate;
use TommasoMusetti\DocStudio\Resources\DocumentTemplateResource\Pages\CreateDocumentTemplate;
use TommasoMusetti\DocStudio\Resources\DocumentTemplateResource\Pages\EditDocumentTemplate;
use TommasoMusetti\DocStudio\Resources\DocumentTemplateResource\Pages\ListDocumentTemplates;
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
