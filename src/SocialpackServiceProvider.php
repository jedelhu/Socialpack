<?php

namespace Jedelhu\Socialpack;

use Illuminate\Support\ServiceProvider;

class SocialpackServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/views/socialpacks', 'socialpacks');

        $this->publishes([
            __DIR__.'/views/socialpacks' => base_path('resources/views/laraveldaily/socialpacks'),
        ]);

        $this->publishes([
            __DIR__.'/Config/socialpack.php' => config_path('socialpack.php'),
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        include __DIR__.'/routes.php';
        $this->app->make('Jedelhu\Socialpack\SocialpackController');

        $this->mergeConfigFrom( __DIR__.'/config/socialpack.php', 'socialpacks');
    }
}
