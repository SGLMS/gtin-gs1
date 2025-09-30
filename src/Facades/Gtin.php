<?php

namespace Sglms\Gs1Gtin\Facades;

use Illuminate\Support\Facades\Facade;

class Gtin extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'gtin';
    }
}