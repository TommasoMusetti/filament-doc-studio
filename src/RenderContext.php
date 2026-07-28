<?php

namespace TommasoMusetti\DocStudio;

use Illuminate\Database\Eloquent\Model;
use TommasoMusetti\DocStudio\Contracts\DocumentDataSource;

/**
 * Everything a block needs to render itself, beyond its own data.
 *
 * ponytail: record + data source only; merge field resolution lands here with
 * the paragraph block.
 */
class RenderContext
{
    public function __construct(
        public readonly ?Model $record = null,
        public readonly ?DocumentDataSource $dataSource = null,
    ) {}
}
