<?php

namespace KolaKachi\Bacs;

use Illuminate\Support\ServiceProvider;

class BacsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }

    public function register()
    {
        // Register package services
    }
}
