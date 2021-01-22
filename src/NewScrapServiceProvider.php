<?php

namespace Abiside\NewScrap;

use Illuminate\Support\ServiceProvider;
use Abiside\NewScrap\NewScraper;

class NewScrapServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('newscrap', function () {
            return new NewScraper();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
