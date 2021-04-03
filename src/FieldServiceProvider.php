<?php

namespace Kraenkvisuell\NovaCmsBlocks;

use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\ServiceProvider;
use Kraenkvisuell\NovaCmsBlocks\Commands\CreateCast;
use Kraenkvisuell\NovaCmsBlocks\Commands\CreateLayout;
use Kraenkvisuell\NovaCmsBlocks\Commands\CreatePreset;
use Kraenkvisuell\NovaCmsBlocks\Commands\CreateResolver;
use Kraenkvisuell\NovaCmsBlocks\Http\Middleware\InterceptBlocksAttributes;

class FieldServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->addMiddleware();
      
        Nova::serving(function (ServingNova $event) {
            Nova::script('nova-cms-blocks', __DIR__.'/../dist/js/field.js');
            Nova::style('nova-cms-blocks', __DIR__.'/../dist/css/field.css');
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (!$this->app->runningInConsole()) return;

        $this->commands([
            CreateCast::class,
            CreateLayout::class,
            CreatePreset::class,
            CreateResolver::class,
        ]);
    }
    
    /**
     * Adds required middleware for Nova requests.
     *
     * @return void
     */
    public function addMiddleware()
    {
        $router = $this->app['router'];
        
        if ($router->hasMiddlewareGroup('nova')) {
            $router->pushMiddlewareToGroup('nova', InterceptBlocksAttributes::class);
            
            return;
        }
        
        if (! $this->app->configurationIsCached()) {
            config()->set('nova.middleware', array_merge(
                config('nova.middleware', []),
                [InterceptBlocksAttributes::class]
            ));
        }
    }
}
