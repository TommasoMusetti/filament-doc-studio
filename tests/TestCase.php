<?php

namespace TommasoMusetti\DocStudio\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;
use TommasoMusetti\DocStudio\DocStudioServiceProvider;
use Workbench\App\Providers\AdminPanelProvider;

class TestCase extends Orchestra
{
    use RefreshDatabase;
    use WithWorkbench;

    protected function getPackageProviders($app)
    {
        $providers = [
            ActionsServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            LivewireServiceProvider::class,
            NotificationsServiceProvider::class,
            SchemasServiceProvider::class,
            SupportServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            DocStudioServiceProvider::class,
            // The same panel `testbench serve` boots, so tests exercise the
            // plugin through a real Filament panel and not a mock of one.
            AdminPanelProvider::class,
        ];

        sort($providers);

        return $providers;
    }

    public function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
    }

    protected function defineDatabaseMigrations(): void
    {
        // Users, for the panel tests.
        $this->loadLaravelMigrations();

        // The same migrations the workbench app serves, so tests and browser
        // share one schema. That folder loads the package's .stub the way a
        // host app would after publishing it.
        $this->loadMigrationsFrom(__DIR__ . '/../workbench/database/migrations');
    }
}
