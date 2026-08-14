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
        return [];
    }

    public function sample(): User
    {
        return new User(['name' => 'Sample Customer']);
    }
}
