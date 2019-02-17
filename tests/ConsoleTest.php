<?php

use Orchestra\Testbench\TestCase;

/** @group no2w */
class ConsoleTest extends TestCase
{

    public function setUp() : void
    {
        parent::setUp();

        config(['database.default' => 'mysql']);
        config(['database.connections.mysql.modes' => [
            'ONLY_FULL_GROUP_BY',
            'STRICT_TRANS_TABLES',
            'NO_ZERO_IN_DATE',
            'NO_ZERO_DATE',
            'ERROR_FOR_DIVISION_BY_ZERO',
            'NO_ENGINE_SUBSTITUTION',
        ]]);

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

        $this->assertNotSubstring("'id' => ", $test);
        $this->assertSubstring("'name' => 'required|max:255'", $test);
        $this->assertSubstring("'address1' => 'required|max:50'", $test);
        $this->assertSubstring("'address2' => 'nullable|max:50'", $test);
        $this->assertSubstring("'country' => 'required|max:2'", $test);
    }

    /** @test */
    public function it_returns_a_list_of_validation_rules_for_a_model()
    {
        ob_start();

        $this->withoutMockingConsoleOutput()
            ->artisan('make:validation', ['--model'=>'Test\Models\Person']);

        $test = ob_get_clean();

        $this->assertNotSubstring("'id' => ", $test);
        $this->assertSubstring("'name' => 'required|max:255'", $test);
        $this->assertSubstring("'address1' => 'required|max:50'", $test);
        $this->assertSubstring("'address2' => 'nullable|max:50'", $test);
        $this->assertSubstring("'country' => 'required|max:2'", $test);
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
        $code = $this->artisan('make:validation', [
            '--model'=>$model
        ])->run();
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

    // /**
    //  * @test
    //  * @group now
    //  */
    // public function foo()
    // {
    //     ob_start();
    //     $code = $this->artisan('make:validation', [
    //         '--model'=>'Test\Models\Comment'
    //     ])->run();
    //     $test = ob_get_clean();

    //     dd($test);
    // }

    /**
     * @test
     * @dataProvider getCommonTypes
     */
    public function it_handles_laravel_data_types($rule, $include=true)
    {
        ob_start();
        $code = $this->artisan('make:validation', [
            '--model'=>'Test\Models\Comment'
        ])->run();
        $test = ob_get_clean();

        if ($include)
            $this->assertSubstring($rule, $test);
        else
            $this->assertNotSubstring($rule, $test);
    }

    public function getCommonTypes()
    {
        return array(
            ["'added_on' => 'required|date'", ],
            ["'added_on_tz' => 'required|date'", ],
            ["'amount_dbl' => 'required|numeric'", ],
            ["'amount_dec' => 'required|numeric|max:999999.99'", ],
            ["'amount_flt' => 'required|numeric'", ],
            ["'amount_ud' => 'required|numeric|max:999999.99'", ],
            ["'birth_year' => 'required|date'", ],
            ["'confirmed' => 'required|boolean'", ],
            ["'created_at' => ", false],
            ["'data' => 'nullable'", ],
            ["'deleted_at' => ", false],
            ["'description_lt' => 'required'", ],
            ["'description_mt' => 'required|max:16777215'", ],
            ["'description_t' => 'nullable|max:65535'", ],
            ["'device' => 'required|max:17'", ],
            ["'happened_at' => 'required|date'", ],
            ["'happened_at_dt' => 'required|date'", ],
            ["'happened_at_dtz' => 'required|date'", ],
            ["'id' => ", false],
            ["'lat' => 'nullable|numeric|max:9999.999999'", ],
            // ["'level' => 'required'", ],
            ["'name_c' => 'required|max:50'", ],
            ["'name_s' => 'required|max:50'", ],
            ["'name_s2' => 'required|max:255'", ],
            ["'options_json' => 'required|json'", ],
            ["'options_jsonb' => 'required|json'", ],
            ["'person_id' => 'required|integer|min:0|max:4294967295'", ],
            ["'sunrise_t' => 'required|date'", ],
            ["'sunrise_ts' => 'required|date'", ],
            // ["'taggable_id' => 'required|max:255'", ],
            // ["'taggable_nm_id' => 'nullable|max:255'", ],
            ["'taggable_type' => 'required|max:255'", ],
            ["'taggable_nm_type' => 'nullable|max:255'", ],
            ["'updated_at' => ", false],
            ["'uuid' => 'nullable|max:36'", ],
            ["'visitor' => 'required|max:45'", ],
            ["'votes_bi' => 'required|numeric'", ],
            ["'votes_i' => 'required|integer|min:-2147483648|max:2147483647'", ],
            ["'votes_mi' => 'required|integer|min:-2147483648|max:2147483647'", ],
            ["'votes_si' => 'required|integer|min:-32768|max:32767'", ],
            ["'votes_ti' => 'required", ],
            // ["'votes_ti' => 'required|min:-128|max:127'", ],
            // ["'votes_ubi' => 'required|min:0'", ],
            ["'votes_ui' => 'required|integer|min:0|max:4294967295", ],
            ["'votes_umi' => 'required|integer|min:0|max:4294967295'", ],
            ["'votes_usi' => 'required|integer|min:0|max:65535'", ],
            ["'votes_uti' => 'required", ],
            // ["'votes_uti' => 'required|min:0|max:255", ],
        );
    }

    protected function getPackageProviders($app)
    {
        return ['Jijoel\ValidationRuleGenerator\ServiceProvider'];
    }

    protected function assertSubstring(string $needle, string $haystack, string $message = '') : void
    {
        $this->assertStringContainsString($needle, $haystack, $message);
    }

    protected function assertNotSubstring(string $needle, string $haystack, string $message = '') : void
    {
        $this->assertStringNotContainsString($needle, $haystack, $message);
    }

}
