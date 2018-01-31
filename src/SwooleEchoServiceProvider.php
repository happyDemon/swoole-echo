<?php

namespace HappyDemon\SwooleEcho;

use HappyDemon\SwooleEcho\Commands\SwooleEcho;
use Illuminate\Support\ServiceProvider;

class SwooleEchoServiceProvider extends ServiceProvider
{
    protected $commands = [
        'cmd.artisan-echo' => SwooleEcho::class,
    ];
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/swoole-echo.php' => config_path('swoole-echo.php')
        ], 'swoole-echo');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/swoole-echo.php', 'swoole-echo');
        $this->registerCommands();
    }

    protected function registerCommands()
    {
        foreach($this->commands as $name => $class)
        {
            $this->app->singleton($name, function ($app) use($name, $class) {
                return $app[$class];
            });
            $this->commands($name);
        }
    }
}