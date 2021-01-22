<?php

namespace Abiside\NewScrap;

use Illuminate\Support\Facades\Facade;

/**
 * Class NewScrapFacade
 * @package Abiside\NewScrap
 */
class NewScrapFacade extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'newscrap';
    }
}