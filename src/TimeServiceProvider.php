<?php

namespace Time\Laravel;

use Illuminate\Support\ServiceProvider;
use Time\Laravel\Commands\GenerateTableConfig;

class TimeServiceProvider extends ServiceProvider
{
	public function boot() {
    		if ($this->app->runningInConsole()) {
        	$this->commands([
            		\Time\Laravel\Commands\GenerateTableConfig::class,
        	]);
        
        	// Esto es vital para que el comando encuentre la migración que creamos
        	$this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    		}
	}
    	
	public function register() {
        // Aquí registraremos el motor de filtros más adelante
    	}
}
