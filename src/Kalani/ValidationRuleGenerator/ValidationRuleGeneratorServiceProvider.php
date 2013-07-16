<?php 

namespace Kalani\ValidationRuleGenerator;

use Illuminate\Support\ServiceProvider;

class ValidationRuleGeneratorServiceProvider extends ServiceProvider {

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
		$this->app['validation-rule-generator'] = $this->app->share(function($app){
			return new ValidationRuleGenerator;
		});

		$this->app->booting(function()
		{
			$loader = \Illuminate\Foundation\AliasLoader::getInstance();
			$loader->alias('ValidationRuleGenerator', 
				'Kalani\ValidationRuleGenerator\Facades\ValidationRuleGenerator');
		});		
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