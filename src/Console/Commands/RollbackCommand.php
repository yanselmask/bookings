<?php

declare(strict_types=1);

namespace Yanselmask\Bookings\Console\Commands;

use Illuminate\Console\Command;

class RollbackCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yanselmask:rollback:bookings {--f|force : Force the operation to run when in production.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback Yanselmask Bookings Tables.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->alert($this->description);

        $path = config('yanselmask.bookings.autoload_migrations') ?
            'vendor/yanselmask/bookings/database/migrations' :
            'database/migrations/yanselmask/bookings';

        if (file_exists($path)) {
            $this->call('migrate:reset', [
                '--path' => $path,
                '--force' => $this->option('force'),
            ]);
        } else {
            $this->warn('No migrations found! Consider publish them first: <fg=green>php artisan yanselmask:publish:bookings</>');
        }

        $this->line('');
    }
}
