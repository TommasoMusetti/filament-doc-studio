<?php

namespace TommasoMusetti\DocStudio;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use TommasoMusetti\DocStudio\Blocks\DocumentBlock;
use TommasoMusetti\DocStudio\Blocks\HeadingBlock;
use TommasoMusetti\DocStudio\Models\DocumentTemplate;

class DocumentRenderer
{
    /**
     * Blocks this renderer knows how to turn into HTML.
     *
     * Deliberately not read from the Filament plugin: documents are rendered
     * from queued jobs and commands too, where no panel is booted. A panel
     * offers a subset of this list, never the other way round — disabling a
     * block in the editor must not break documents already saved with it.
     *
     * @var array<class-string<DocumentBlock>>
     */
    protected array $blocks = [
        HeadingBlock::class,
    ];

    /**
     * @param  class-string<DocumentBlock>  ...$blocks
     */
    public function register(string ...$blocks): static
    {
        foreach ($blocks as $block) {
            if (! is_subclass_of($block, DocumentBlock::class)) {
                throw new InvalidArgumentException("[{$block}] is not a " . DocumentBlock::class . '.');
            }
        }

        $this->blocks = array_values(array_unique([...$this->blocks, ...$blocks]));

        return $this;
    }

    /**
     * @return array<class-string<DocumentBlock>>
     */
    public function blocks(): array
    {
        return $this->blocks;
    }

    public function html(DocumentTemplate $template, ?RenderContext $context = null): string
    {
        $context ??= new RenderContext;

        $body = collect($template->blocks ?? [])
            ->map(fn (array $block): string => $this->renderBlock($block, $context))
            ->implode('');

        return view('doc-studio::document', ['body' => $body])->render();
    }

    /**
     * @return string Raw PDF bytes.
     */
    public function pdf(DocumentTemplate $template, ?RenderContext $context = null): string
    {
        $options = new Options;
        // A template is user written content: never let it make the server
        // fetch a URL of its choosing.
        $options->setIsRemoteEnabled(false);

        $dompdf = new Dompdf($options);
        $dompdf->setPaper(
            Arr::get($template->page_settings ?? [], 'paper', 'a4'),
            Arr::get($template->page_settings ?? [], 'orientation', 'portrait'),
        );
        $dompdf->loadHtml($this->html($template, $context));
        $dompdf->render();

        return (string) $dompdf->output();
    }

    /**
     * @param  array<string, mixed>  $block
     */
    protected function renderBlock(array $block, RenderContext $context): string
    {
        $class = $this->blockClass($block['type'] ?? null);

        return app($class)->render($block['data'] ?? [], $context);
    }

    /**
     * @return class-string<DocumentBlock>
     */
    protected function blockClass(?string $type): string
    {
        foreach ($this->blocks as $class) {
            if ($class::name() === $type) {
                return $class;
            }
        }

        throw new InvalidArgumentException("Unknown document block [{$type}].");
    }
}
