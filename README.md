# Document Studio

[![Tests](https://img.shields.io/github/actions/workflow/status/TommasoMusetti/doc-studio/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/TommasoMusetti/doc-studio/actions?query=workflow%3Atests+branch%3Amain)
[![License](https://img.shields.io/packagist/l/tommasomusetti/filament-doc-studio.svg?style=flat-square)](LICENSE.md)

A PDF template builder for end users, inside Filament. Your client drags blocks
into a template, drops in merge fields, and prints a record as a PDF — without a
developer touching a Blade file for every "can you change the quote layout?".

> **Work in progress.** The vertical walk is standing up: a template renders to a
> PDF through dompdf. No editor UI yet.

## Status

| | |
|---|---|
| Blocks | heading |
| Engine | dompdf (pure PHP, no binaries to install) |
| Editor | not wired up yet |

## Installation

```bash
composer require tommasomusetti/filament-doc-studio
php artisan doc-studio:install
```

The install command publishes the migration and offers to run it. To do it by hand:

```bash
php artisan vendor:publish --tag="doc-studio-migrations"
php artisan migrate
```

## Usage

```php
use TommasoMusetti\DocStudio\DocumentRenderer;
use TommasoMusetti\DocStudio\Models\DocumentTemplate;

$template = DocumentTemplate::create([
    'name' => 'Quote',
    'slug' => 'quote',
    'target_model' => \App\Models\Order::class,
    'blocks' => [
        ['type' => 'heading', 'data' => ['text' => 'Quote 2026/001', 'level' => 1]],
    ],
    'page_settings' => ['paper' => 'a4', 'orientation' => 'portrait'],
]);

$pdf = app(DocumentRenderer::class)->pdf($template); // raw PDF bytes
```

## Testing

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
