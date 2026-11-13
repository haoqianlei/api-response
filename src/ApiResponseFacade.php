<?php

namespace Back\ApiResponse;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Back\ApiResponse\ApiResponse
 */
class ApiResponseFacade extends Facade
{
    protected static $cached = false;

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'api-response';
    }
}
