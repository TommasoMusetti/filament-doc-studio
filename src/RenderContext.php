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
}
