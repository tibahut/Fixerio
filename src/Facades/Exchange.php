<?php

namespace Tibahut\Fixerio\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Tibahut\Fixerio\Exchange
 */
class Exchange extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'exchange';
    }
}
