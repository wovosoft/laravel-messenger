<?php

namespace Wovosoft\LaravelMessenger\Tests;

use Wovosoft\LaravelMessenger\Facades\LaravelMessenger;
use Wovosoft\LaravelMessenger\ServiceProvider;
use Orchestra\Testbench\TestCase;

class LaravelMessengerTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'laravel-messenger' => LaravelMessenger::class,
        ];
    }

    public function testExample()
    {
        $this->assertEquals(1, 1);
    }
}
