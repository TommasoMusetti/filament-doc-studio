<?php

use Filament\Panel;
use TommasoMusetti\DocStudio\DocStudioPlugin;
use TommasoMusetti\DocStudio\Models\DocumentTemplate;
use TommasoMusetti\DocStudio\Resources\DocumentTemplateResource;
use TommasoMusetti\DocStudio\Resources\DocumentTemplateResource\Pages;

it('registers the resource into a panel', function () {
    $panel = Panel::make()->id('admin');

    DocStudioPlugin::make()->register($panel);

    expect($panel->getResources())->toContain(DocumentTemplateResource::class);
});

it('points the resource at the template model', function () {
    expect(DocumentTemplateResource::getModel())->toBe(DocumentTemplate::class);
});

it('exposes list, create and edit pages', function () {
    expect(array_keys(DocumentTemplateResource::getPages()))
        ->toBe(['index', 'create', 'edit']);
});

it('wires every page back to its resource', function () {
    foreach ([Pages\ListDocumentTemplates::class, Pages\CreateDocumentTemplate::class, Pages\EditDocumentTemplate::class] as $page) {
        expect($page::getResource())->toBe(DocumentTemplateResource::class);
    }
});
