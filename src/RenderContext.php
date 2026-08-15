<?php

namespace TommasoMusetti\DocStudio;

use Illuminate\Database\Eloquent\Model;
use TommasoMusetti\DocStudio\Contracts\DocumentDataSource;

/**
 * Everything a block needs to render itself, beyond its own data.
 */
class RenderContext
{
    public function __construct(
        public readonly ?Model $record = null,
        public readonly ?DocumentDataSource $dataSource = null,
    ) {}

    /**
     * Resolves a merge field key against the current record.
     *
     * Never throws: a template can outlive the data source field it points
     * at (host app narrowed the whitelist, or record/data source is simply
     * absent, e.g. a bare render() call in a test), and a stale document
     * must still render rather than fail at print time.
     */
    public function resolveField(string $key): string
    {
        if ($this->record === null || $this->dataSource === null) {
            return '';
        }

        $field = $this->dataSource->fields()[$key] ?? null;

        if ($field === null) {
            return '';
        }

        return (string) ($field['resolver'])($this->record);
    }

    /**
     * Resolves a collection key against the current record.
     *
     * Column labels are static per data source, not per record: they still
     * come back with no record in context (e.g. rendering outside a
     * specific print run), so a table at least shows its headers. Rows are
     * the part that genuinely needs a record. Same never-throws contract as
     * resolveField() otherwise: an unknown key or a missing data source just
     * mean an empty table rather than a broken document.
     *
     * @return array{columns: array<string, string>, rows: array<int, array<string, mixed>>}
     */
    public function resolveCollection(string $key): array
    {
        $collection = $this->dataSource?->collections()[$key] ?? null;

        if ($collection === null) {
            return ['columns' => [], 'rows' => []];
        }

        if ($this->record === null) {
            return ['columns' => $collection['columns'], 'rows' => []];
        }

        $rows = collect(($collection['resolver'])($this->record))
            ->map(fn (mixed $row): array => (array) $row)
            ->all();

        return ['columns' => $collection['columns'], 'rows' => $rows];
    }
}
