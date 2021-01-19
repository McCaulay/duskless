<?php

namespace McCaulay\Duskless;

use Exception;
use Illuminate\Support\ServiceProvider;

class DusklessServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register any package services.
     *
     * @return void
     * @throws \Exception
     */
    public function register()
    {
        // Register the classes to use with the facade
        $this->app->bind('duskless', 'McCaulay\Duskless\Duskless');

        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\InstallCommand::class,
                Console\PageCommand::class,
                Console\ComponentCommand::class,
                Console\ChromeDriverCommand::class,
            ]);
        }
    }
}