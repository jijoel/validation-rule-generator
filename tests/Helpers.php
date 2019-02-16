<?php

use Jijoel\ValidationRuleGenerator\Generator;


trait Helpers
{
    public $ruleGenerator;

    public function setUp() : void
    {
        parent::setUp();

        $this->setupApp();
        $this->setupSchemaManager();

        $this->ruleGenerator = new Generator();
    }

    public function tearDown() : void
    {
        Mockery::close();
    }

    public function setupApp()
    {
        $app = Mockery::mock('AppMock');
        $app->shouldReceive('instance')->once()->andReturn($app);
        Illuminate\Support\Facades\Facade::setFacadeApplication($app);
    }

    public function setupSchemaManager()
    {
        Illuminate\Support\Facades\DB::swap($db = Mockery::mock('DBMock'));
        $db->shouldReceive('connection')->andReturn(new DbConnectionStub);
    }

    public function columnRules($col, $overrides = array())
    {
        return $this->ruleGenerator->getColumnRules('foo', $col, $overrides);
    }

    public function tableRules($overrides = array())
    {
        return $this->ruleGenerator->getTableRules('foo', $overrides);
    }

}
