<?php

use Orchestra\Testbench\TestCase;

/** @group now */
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
    public function it_returns_a_list_of_validation_rules_for_a_table()
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

    /** @test */
    public function it_returns_a_list_of_validation_rules_for_a_model()
    {
        ob_start();

        $this->withoutMockingConsoleOutput()
            ->artisan('make:validation', ['--model'=>'Test\Models\Person']);

        $test = ob_get_clean();

        $this->assertStringContainsString("'id' => 'required|unique:people,id'", $test);
        $this->assertStringContainsString("'name' => 'required'", $test);
        $this->assertStringContainsString("'address1' => 'required'", $test);
        $this->assertStringContainsString("'country' => 'required'", $test);
    }

    /**
     * @test
     * @dataProvider getModelSignatures
     */
    public function it_has_several_ways_to_instantiate_a_model($model)
    {
        // TODO: Mock app container
        // Container::getInstance()->getNamespace()
        // $this->app->shouldReceive('getNamespace')->once()->andReturn('Test');

        ob_start();
        $code = $this->artisan('make:validation', ['--model'=>$model])->run();
        $test = ob_get_clean();

        $this->assertEquals(0, $code);
    }

    public function getModelSignatures()
    {
        return array(
            ['Test/Models/Person'],
            ['Test\Models\Person'],
            // ['Person'],    // TODO: When we can get the namespace
        );
    }


    protected function getPackageProviders($app)
    {
        return ['Jijoel\ValidationRuleGenerator\ServiceProvider'];
    }

}
