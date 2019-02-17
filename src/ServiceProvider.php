<?php

namespace Jijoel\ValidationRuleGenerator;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Illuminate\Foundation\AliasLoader;


class ServiceProvider extends LaravelServiceProvider
{

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerGenerator();
		$this->registerCommand();
	}

	protected function registerGenerator()
	{
        $this->app->singleton(Generator::class, function ($app) {
            return new Generator;
        });


		$this->app->booting(function()
		{
			$loader = AliasLoader::getInstance();

			$loader->alias(
				'ValidationRuleGenerator',
				Facade::class
			);
		});
	}

	protected function registerCommand()
	{
	    if ($this->app->runningInConsole()) {
	        $this->commands([
	            MakeValidationCommand::class,
	        ]);
	    }
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
