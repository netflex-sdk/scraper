<?php

namespace Netflex\Scraper\Facades;

use Illuminate\Support\Facades\Facade;
use Netflex\API\Facades\API;

/**
 * @method static \Netflex\Scraper\Metadata|bool scrape(string $url)
 * @package Netflex\Scraper\Facades
 */
class Scraper extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'netflex.scraper';
    }
}
