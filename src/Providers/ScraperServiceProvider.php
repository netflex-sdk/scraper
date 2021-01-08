<?php

namespace Netflex\Scraper\Providers;

use Illuminate\Support\ServiceProvider;
use Netflex\Scraper\Scraper;

class ScraperServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('netflex.scraper', function () {
            return new Scraper();
        });
    }

    public function boot()
    {
        //
    }
}
