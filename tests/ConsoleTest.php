<?php

use Orchestra\Testbench\TestCase;


class ConsoleTest extends TestCase
{

    public function setUp() : void
    {
        parent::setUp();

        config(['database.default' => 'testing']);

        $this->loadMigrationsFrom(__DIR__ . '/migrations');

        $a = $this->artisan('migrate')->run();
    }

    /** @test */
    public function it_returns_an_error_if_option_not_set()
    {
        $this->artisan('make:validation')
            ->expectsOutput('Please specify table or model to generate');
    }

    /** @test */
    public function it_returns_a_list_of_validation_rules()
    {
        ob_start();

        $this->withoutMockingConsoleOutput()
            ->artisan('make:validation', ['--table'=>'people']);

        $test = ob_get_clean();

        $this->assertStringContainsString("'id' => 'required|unique:people,id'", $test);
        $this->assertStringContainsString("'name' => 'required'", $test);
        $this->assertStringContainsString("'address1' => 'required'", $test);
        $this->assertStringContainsString("'country' => 'required'", $test);
    }


    protected function getPackageProviders($app)
    {
        return ['Jijoel\ValidationRuleGenerator\ServiceProvider'];
    }

}
