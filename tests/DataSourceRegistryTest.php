<?php

use TommasoMusetti\DocStudio\DocumentRenderer;
use TommasoMusetti\DocStudio\Tests\Fixtures\TestDataSource;
use Workbench\App\Models\User;

class NotADataSource {}

it('resolves a data source registered for its model', function () {
    app(DocumentRenderer::class)->registerDataSource(TestDataSource::class);

    expect(app(DocumentRenderer::class)->dataSourceFor(User::class))
        ->toBeInstanceOf(TestDataSource::class);
});

it('returns null for a model with no data source registered', function () {
    expect(app(DocumentRenderer::class)->dataSourceFor('App\\Models\\Nothing'))->toBeNull();
});

it('refuses to register something that is not a data source', function () {
    expect(fn () => app(DocumentRenderer::class)->registerDataSource(NotADataSource::class))
        ->toThrow(InvalidArgumentException::class);
});

it('shares one renderer, so a data source registration survives', function () {
    app(DocumentRenderer::class)->registerDataSource(TestDataSource::class);

    expect(app(DocumentRenderer::class)->dataSourceFor(User::class))->not->toBeNull();
});
