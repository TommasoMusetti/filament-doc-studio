<?php

namespace TommasoMusetti\DocStudio\Tests\Fixtures;

use TommasoMusetti\DocStudio\Contracts\DocumentDataSource;
use Workbench\App\Models\User;

/**
 * A data source a host app would write, used to test merge field resolution.
 */
class TestDataSource implements DocumentDataSource
{
    public static function model(): string
    {
        return User::class;
    }

    public function fields(): array
    {
        return [
            'customer_name' => [
                'label' => 'Customer name',
                'resolver' => fn (User $user): string => $user->name,
            ],
        ];
    }

    public function collections(): array
    {
        return [
            'items' => [
                'label' => 'Line items',
                'columns' => ['name' => 'Item', 'qty' => 'Qty'],
                'resolver' => fn (User $user): array => [
                    ['name' => 'Widget', 'qty' => 2],
                    ['name' => 'Gadget', 'qty' => 1],
                ],
            ],
        ];
    }

    public function sample(): User
    {
        return new User(['name' => 'Sample Customer']);
    }
}
