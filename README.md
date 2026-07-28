# Document Studio

[![Tests](https://img.shields.io/github/actions/workflow/status/TommasoMusetti/filament-doc-studio/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/TommasoMusetti/filament-doc-studio/actions?query=workflow%3Atests+branch%3Amain)
[![License](https://img.shields.io/github/license/TommasoMusetti/filament-doc-studio?style=flat-square)](LICENSE.md)

A PDF template builder for end users, inside Filament. Your client drags blocks
into a template and prints a record as a PDF — without a developer touching a
Blade file for every "can you change the quote layout?".

> **Work in progress.** The editor and the PDF pipeline are connected end to
> end, with one block. Merge fields and the line item table are next.

## Status

| | |
|---|---|
| Editor | Filament Builder field, inside your panel |
| Blocks | heading |
| Merge fields | not yet |
| Engine | dompdf (pure PHP, nothing to install on the server) |

## Installation

```bash
composer require tommasomusetti/filament-doc-studio
php artisan doc-studio:install
```

The install command publishes the migration and offers to run it. By hand:

```bash
php artisan vendor:publish --tag="doc-studio-migrations"
php artisan migrate
```

Then add the plugin to the panel that should get the editor:

```php
use TommasoMusetti\DocStudio\DocStudioPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugins([
            DocStudioPlugin::make(),
        ]);
}
```

Templates now live under **Document templates** in that panel.

## Rendering a PDF

```php
use TommasoMusetti\DocStudio\DocumentRenderer;
use TommasoMusetti\DocStudio\Models\DocumentTemplate;

$template = DocumentTemplate::where('slug', 'quote')->firstOrFail();

$pdf = app(DocumentRenderer::class)->pdf($template); // raw PDF bytes
```

The renderer never touches Filament, so this works from a queued job or a
console command as well as from a panel.

## Restricting the blocks a panel offers

```php
DocStudioPlugin::make()->blocks([
    HeadingBlock::class,
]);
```

A panel can only narrow the list. It cannot offer a block the renderer does not
know: disabling a block must never break templates already saved with it.

## Adding your own block

A block is one class with two faces — an editor field set, and print HTML:

```php
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use TommasoMusetti\DocStudio\Blocks\DocumentBlock;
use TommasoMusetti\DocStudio\RenderContext;

class StampBlock extends DocumentBlock
{
    public static function make(): Block
    {
        return Block::make('stamp')->schema([
            TextInput::make('text')->required(),
        ]);
    }

    public function render(array $data, RenderContext $context): string
    {
        return '<p class="stamp">' . e($data['text'] ?? '') . '</p>';
    }
}
```

Register it with the renderer, in a service provider's `boot()`:

```php
app(DocumentRenderer::class)->register(StampBlock::class);
```

Write the HTML dompdf first: table layouts, conservative CSS, no flexbox or
grid. And escape anything the user typed — a template is user written content.

## Local development

`workbench/` is a small Laravel app with a real Filament panel, so the plugin
can be opened in a browser:

```bash
composer serve   # http://127.0.0.1:8000/admin
```

Log in with `test@example.com` / `password`. The same panel is registered in the
test suite, so the panel tests run against what you see in the browser.

```bash
composer test
composer analyse
composer lint
```

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md).

## Security

Please review [our security policy](.github/SECURITY.md) on how to report security
vulnerabilities.

## Credits

- [Tommaso Musetti](https://github.com/TommasoMusetti)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
