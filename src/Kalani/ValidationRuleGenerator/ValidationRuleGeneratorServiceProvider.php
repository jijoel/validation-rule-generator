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
		$this->registerGenerator();
		$this->registerCommand();
	}

	protected function registerGenerator()
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

	protected function registerCommand()
	{
		$this->app['validation-rule-generator-command'] = $this->app->share(function($app){
			$generator = new ValidationRuleGenerator;
			return new ValidationRuleGeneratorCommand($generator);
		});

		$this->commands('validation-rule-generator-command');
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