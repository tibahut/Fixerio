<?php

namespace tibahut\Fixerio;

use Illuminate\Support\ServiceProvider;

class ExchangeServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->singleton(Exchange::class, function ($app) {
            return (new Exchange())->key(config('services.fixer.key'));
        });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [Exchange::class];
    }
}
