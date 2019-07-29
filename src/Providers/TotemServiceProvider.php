<?php

namespace Bertshang\Scheduler\Providers;

use Cron\CronExpression;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Bertshang\Scheduler\Contracts\TaskInterface;
use Bertshang\Scheduler\Console\Commands\ListSchedule;
use Bertshang\Scheduler\Repositories\EloquentTaskRepository;

class TotemServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerResources();
        $this->defineAssetPublishing();

        Validator::extend('cron_expression', function ($attribute, $value, $parameters, $validator) {
            return CronExpression::isValidExpression($value);
        });
    }

    /**
     * Register any services.
     *
     * @return void
     */
    public function register()
    {
        if (! defined('TOTEM_PATH')) {
            define('TOTEM_PATH', realpath(__DIR__.'/../../'));
        }

        $this->commands([
            ListSchedule::class,
        ]);

        $this->app->bindIf('totem.tasks', EloquentTaskRepository::class, true);
        $this->app->alias('totem.tasks', TaskInterface::class);
        $this->app->register(TotemEventServiceProvider::class);

        $this->mergeConfigFrom(
            __DIR__.'/../../config/totem.php',
            'totem'
        );

        try {
            if (Schema::hasTable(config('totem.table_prefix').'tasks')) {
                $this->app->register(ConsoleServiceProvider::class);
            }
        } catch (\PDOException $ex) {
            // This will trigger if DB cannot be connected to
            Log::error($ex->getMessage());
        }
    }

    /**
     * Register the Totem resources.
     *
     * @return void
     */
    protected function registerResources()
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }

    /**
     * Define the asset publishing configuration.
     *
     * @return void
     */
    public function defineAssetPublishing()
    {
        $this->publishes([
            TOTEM_PATH.'/config' => config_path()
        ], 'totem-config');
    }
}
