<?php

namespace TommasoMusetti\DocStudio;

use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DocStudioServiceProvider extends PackageServiceProvider
{
    public static string $name = 'doc-studio';

    public static string $viewNamespace = 'doc-studio';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasViews(static::$viewNamespace)
            ->hasMigration('create_document_templates_table')
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('TommasoMusetti/filament-doc-studio');
            });
    }
}
