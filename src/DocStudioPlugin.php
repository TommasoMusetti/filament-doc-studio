<?php

namespace TommasoMusetti\DocStudio;

use Filament\Contracts\Plugin;
use Filament\Panel;
use LogicException;
use TommasoMusetti\DocStudio\Blocks\DocumentBlock;
use TommasoMusetti\DocStudio\Resources\DocumentTemplateResource;

class DocStudioPlugin implements Plugin
{
    /**
     * @var array<class-string<DocumentBlock>>|null
     */
    protected ?array $blocks = null;

    public function getId(): string
    {
        return 'doc-studio';
    }

    /**
     * Restrict which blocks this panel offers in the editor.
     *
     * Defaults to everything the renderer knows. A panel can only ever narrow
     * that list: offering a block the renderer cannot turn into a PDF would
     * hand the user a template that fails at print time.
     *
     * @param  array<class-string<DocumentBlock>>  $blocks
     */
    public function blocks(array $blocks): static
    {
        $this->blocks = $blocks;

        return $this;
    }

    /**
     * @return array<class-string<DocumentBlock>>
     */
    public function getBlocks(): array
    {
        return $this->blocks ?? app(DocumentRenderer::class)->blocks();
    }

    public function register(Panel $panel): void
    {
        // Fail here, while a developer is watching a panel boot, rather than
        // months later when someone clicks "generate" on a saved template.
        $renderable = app(DocumentRenderer::class)->blocks();

        foreach ($this->blocks ?? [] as $block) {
            if (in_array($block, $renderable, true)) {
                continue;
            }

            throw new LogicException(
                "Block [{$block}] is not registered with " . DocumentRenderer::class . ', so panel ' .
                "[{$panel->getId()}] would offer a block that cannot be rendered."
            );
        }

        $panel->resources([
            DocumentTemplateResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
