<?php

namespace Jankado\Sdk\Facades;

use Illuminate\Support\Facades\Facade;
use Jankado\Sdk\Client;

class Jankado extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Client::class;
    }
}
