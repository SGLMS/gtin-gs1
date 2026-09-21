<?php

namespace Sglms\Gs1Gtin;

use Illuminate\Support\ServiceProvider;

class Gs1ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('gs1', static fn () => new Gs1);
        $this->app->singleton('gtin', static fn () => new Gtin);
    }

    public function boot(): void
    {
        //
    }
}
