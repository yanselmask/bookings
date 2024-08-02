<?php

declare(strict_types=1);

namespace Yanselmask\Bookings\Providers;

use Illuminate\Support\ServiceProvider;
use Rinvex\Support\Traits\ConsoleTools;
use Illuminate\Database\Schema\Blueprint;
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
        Blueprint::macro('bookings', function () {
            $this->integer('price')->default(0);
            $this->string('unit')->default('day');
        });

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
        Blueprint
        // Publish Resources
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('yanselmask-booking.php')
        ], 'yanselmask-booking-configuration');
        $this->publishesMigrations([
            __DIR__.'/../database/migrations/' => database_path('migrations')
        ], 'yanselmask-booking-migrations');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}
