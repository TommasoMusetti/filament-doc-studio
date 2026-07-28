<?php

namespace TommasoMusetti\DocStudio\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Declares which model data a template is allowed to expose.
 *
 * This whitelist is the security boundary for merge fields: only what a data
 * source returns here can ever reach a rendered document.
 */
interface DocumentDataSource
{
    /**
     * @return class-string<Model>
     */
    public static function model(): string;

    /**
     * Single values, offered to the editor as merge fields.
     *
     * @return array<string, array{label: string, resolver: callable(Model): string}>
     */
    public function fields(): array;

    /**
     * Repeatable rows, offered to the editor as line item tables.
     *
     * @return array<string, array{label: string, columns: array<string, string>, resolver: callable(Model): iterable}>
     */
    public function collections(): array;

    /**
     * Demo record the editor preview renders against.
     */
    public function sample(): Model;
}
