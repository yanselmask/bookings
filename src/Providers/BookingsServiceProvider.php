<?php

declare(strict_types=1);

namespace Yanselmask\Bookings\Providers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;
use Rinvex\Support\Traits\ConsoleTools;
use Yanselmask\Bookings\Console\Commands\MigrateCommand;
use Yanselmask\Bookings\Console\Commands\PublishCommand;
use Yanselmask\Bookings\Console\Commands\RollbackCommand;

class BookingsServiceProvider extends ServiceProvider
{
    use ConsoleTools;

    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $commands = [
        MigrateCommand::class,
        PublishCommand::class,
        RollbackCommand::class,
    ];

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(realpath(__DIR__.'/../../config/config.php'), 'yanselmask.bookings');

        if ($this->app->runningInConsole()) {
            // Register console commands
            $this->commands($this->commands);
        }
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Blueprint::macro('bookings', function () {
            $this->integer('price')->nullable();
            $this->integer('base_cost')->nullable();
            $this->integer('unit_cost')->nullable();
            $this->string('unit')->nullable();
            $this->string('currency', 3);
        });

        // Publish Resources
        $this->publishes([
            __DIR__.'/../../config/config.php' => config_path('bookings.php'),
        ], 'yanselmask-booking-configuration');
        $this->publishesMigrations([
            __DIR__.'/../../database/migrations/' => database_path('migrations')
        ], 'yanselmask-booking-migrations');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}
