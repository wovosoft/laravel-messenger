<?php

namespace Wovosoft\LaravelMessenger\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelMessenger extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'LaravelMessenger';
    }
}
