<?php

namespace Workbench\App\DataSources;

use TommasoMusetti\DocStudio\Contracts\DocumentDataSource;
use Workbench\App\Models\User;

/**
 * The data source a host app would write, registered here only so the
 * merge tag picker and preview have something real to show in the browser.
 */
class UserDataSource implements DocumentDataSource
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
            'customer_email' => [
                'label' => 'Customer email',
                'resolver' => fn (User $user): string => $user->email,
            ],
        ];
    }

    public function collections(): array
    {
        return [];
    }

    public function sample(): User
    {
        return new User(['name' => 'Ada Lovelace', 'email' => 'ada@example.com']);
    }
}
