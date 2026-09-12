<?php

namespace Jankado\Sdk;

use Illuminate\Support\ServiceProvider;

class JankadoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/jankado.php', 'jankado');

        $this->app->singleton(Client::class, fn () => new Client(
            token: (string) config('jankado.token'),
            baseUrl: (string) config('jankado.base_url'),
        ));
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/jankado.php' => config_path('jankado.php'),
        ], 'jankado-config');
    }
}
