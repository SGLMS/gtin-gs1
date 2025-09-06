<?php

namespace Sglms\Gs1Gtin;


use Illuminate\Support\ServiceProvider;

class Gs1ServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('gs1', function ($app) {
            return new Gs1();
        });
    }

    public function boot()
    {
        //
    }
}
