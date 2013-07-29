<?php

use Illuminate\Support\Facades\DB;
use Kalani\ValidationRuleGenerator\ValidationRuleGenerator;
use Mockery as m;


class ValidationRuleGeneratorTest extends PHPUnit_Framework_TestCase
{
    protected $ruleGenerator;

    public function setUp()
    {
        parent::setUp();
        $this->setupApp();
        $this->setupSchemaManager();

        $this->ruleGenerator = new ValidationRuleGenerator();
    }

    public function tearDown()
    {
        m::close();
    }

    /**
     * Test to make sure we can pass in a schema manager
     */
    public function testShouldGetSchemaManager()
    {
        $test = new ValidationRuleGenerator(Null);
        $result = print_r($test, true);
        $this->assertContains('SchemaManagerStub Object', $result);

        $test = new ValidationRuleGenerator('foo');
        $result = print_r($test, true);
        $this->assertNotContains('SchemaManagerStub Object', $result);
        $this->assertContains('foo', $result);
    }

    public function testGetColumnRules()
    {
        $codeRules = $this->columnRules('code');
        $this->assertContains('max:4', $codeRules);
        $this->assertContains('required', $codeRules);
        $this->assertContains('unique:foo,code', $codeRules);
    }

    public function testGetColumnRulesIncludesUnique()
    {
        $this->assertContains('unique',    $this->columnRules('code'));     
        $this->assertContains('unique',    $this->columnRules('col1'));     
        $this->assertNotContains('unique', $this->columnRules('col2'));   
    }

    public function testUniqueIdsForColumn()
    {
        $rules = $this->ruleGenerator->getColumnRules('foo', 'code', Null);
        $test = $this->ruleGenerator->getColumnUniqueRules($rules, 12);
        $this->assertContains('unique:foo,code,12,id', $test);

        $test = $this->ruleGenerator->getColumnUniqueRules($rules, 17, 'buzz');
        $this->assertContains('unique:foo,code,17,buzz', $test);

        $test = $this->ruleGenerator->getUniqueRules($rules, 14);
        $this->assertContains('unique:foo,code,14,id', $test);

        $rules = 'foo:bazz|unique:fizz,bazz|buzz';
        $test = $this->ruleGenerator->getUniqueRules($rules, 12);
        $this->assertEquals('foo:bazz|unique:fizz,bazz,12,id|buzz', $test);
    }
    

    public function testGetColumnRulesIncludesRequired()
    {
        $this->assertContains('required',    $this->columnRules('code'));   
        $this->assertContains('required',    $this->columnRules('col1'));
        $this->assertNotContains('required', $this->columnRules('col2'));
    }

    public function testGetColumnRulesIncludesMaxOnlyIfFound()
    {
        $this->assertContains('max:', $this->columnRules('col1'));
        $this->assertNotContains('max:', $this->columnRules('col2'));
    }

    public function testGetColumnRulesIncludesPassedInRule()
    {
        $r = $this->columnRules('code', array('bazz'));
        $this->assertContains('bazz', $r);
    }

    public function testGetColumnRulesCanBeOverridden()
    {
        $this->assertContains('max:4',$this->columnRules('code'));
        $this->assertContains('max:2',$this->columnRules('code', array('max:2')));
        $this->assertContains('unique:fizz,buzz',
            $this->columnRules('code', array('unique:fizz,buzz')));
    }

    public function testCanGetTableRules()
    {
        $tableRules = $this->ruleGenerator->getTableRules('foo');
        $this->assertEquals(3, count($tableRules));
    }

    public function testGetTableRulesIncludesUnique()
    {
        $tableRules = $this->ruleGenerator->getTableRules('foo');
        $this->assertContains('unique', $tableRules['code']);
        $this->assertContains('unique', $tableRules['col1']);
        $this->assertNotContains('unique', $tableRules['col2']);
    }

    public function testGetTableRulesMatchColumnRules()
    {
        $tableRules = $this->ruleGenerator->getTableRules('foo');
        $columnRules = $this->columnRules('code');
        $this->assertEquals($columnRules, $tableRules['code']);
    }

    public function testGetTableRulesIncludesPassedInRule()
    {
        $r = $this->ruleGenerator->getTableRules('foo', array(
            'bazz' => array('foo:bar'),
        ));
        $this->assertEquals('foo:bar', $r['bazz']);
    }

    public function testGetTableRulesCanBeOverridden()
    {
        $testRules = array(
            'code' => array('unique:fizz,buzz'),
        );

        $r1 = $this->tableRules();
        $r2 = $this->tableRules($testRules);
        $this->assertContains('unique:foo,code', $r1['code']);
        $this->assertNotContains('unique:foo,code', $r2['code']);
        $this->assertContains('unique:fizz,buzz', $r2['code']);
    }

    public function testUniqueIdsForTable()
    {
        $rules = $this->ruleGenerator->getTableRules('foo', Null);
        $test = $this->ruleGenerator->getUniqueRules($rules, 12);
        $this->assertContains('unique:foo,code,12,id', $test['code']);
        $test = $this->ruleGenerator->getUniqueRules($rules, 14, 'bazz');
        $this->assertContains('unique:foo,code,14,bazz', $test['code']);
    }

    public function testGetAllTableRules()
    {
        $test = $this->ruleGenerator->getAllTableRules();
        $this->assertEquals(2, count($test));
        $this->assertArrayHasKey('foo', $test);
    }

    public function testGetRulesReturnsCorrectStuff()
    {
        $this->assertEquals($this->ruleGenerator->getRules(), 
            $this->ruleGenerator->getAllTableRules());

        $this->assertEquals($this->ruleGenerator->getRules('table'),
            $this->ruleGenerator->getTableRules('table'));

        $this->assertEquals($this->ruleGenerator->getRules('table', 'col1'),
            $this->ruleGenerator->getColumnRules('table', 'col1'));
    }

    public function testGetRulesCanIncludeOverride()
    {
        $overrides = array(
            'bazz' => 'foo:bar',
            'code'  => 'max:12',
        );
        $test = $this->ruleGenerator->getRules('table', Null, $overrides);
        $this->assertArrayHasKey('bazz', $test);
        $this->assertContains('max:12', $test['code']);
    }

    public function testGetRulesCanIncludeId()
    {
        $test = $this->ruleGenerator->getRules('table', Null, Null, 15);
        $this->assertContains('15', $test['code']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSendBadDataToGetRules()
    {
        $test = $this->ruleGenerator->getRules(Null, 'col');
    }

    public function testStaticMake()
    {
        // ValidationRuleGenerator::make();
        $test = $this->ruleGenerator;
        $t2   = $this->ruleGenerator->make();
        $this->assertEquals($test, $t2);
    }




// Protected helper functions ---------------------------------------

    protected function setupApp()
    {
        $app = m::mock('AppMock');
        $app->shouldReceive('instance')->once()->andReturn($app);
        Illuminate\Support\Facades\Facade::setFacadeApplication($app);
    }

    protected function setupSchemaManager()
    {
        Illuminate\Support\Facades\DB::swap($db = m::mock('DBMock'));
        $db->shouldReceive('connection')->andReturn(new DbConnectionStub);
    }

    private function columnRules($col, $overrides = array())
    {
        return $this->ruleGenerator->getColumnRules('foo', $col, $overrides);
    }

    private function tableRules($overrides = array())
    {
        return $this->ruleGenerator->getTableRules('foo', $overrides);
    }

}






// Helper Classes ---------------------------------------------------

class DbConnectionStub
{
    public function getDoctrineSchemaManager() { return new SchemaManagerStub; }
    
    public function getDoctrineColumn($table, $col) 
    { 
        if ($col=='code')
            return new SchemaColCodeStub;
        elseif ($col == 'col1')
            return new SchemaCol1Stub; 
        elseif ($col == 'col2')
            return new SchemaCol2Stub;

        throw new \InvalidArgumentException; 
    }
}

class SchemaManagerStub 
{
    public function listTableNames() 
    { 
        return array('foo', 'bar'); 
    }

    public function listTableColumns($table) 
    { 
        return array(
            'col1' => new SchemaCol1Stub, 
            'col2' => new SchemaCol2Stub,
            'code' => new SchemaColCodeStub,
        ); 
    }

    public function listTableIndexes($table) 
    { 
        return array(
            'primary'=> new SchemaIndexStub, 
            'code' => new SchemaIndexCodeStub,
            'col2_index'=> new SchemaIndexCol2Stub,
        ); 
    }
}

class SchemaCol1Stub
{
    public function getName()  { return 'col1'; }
    public function getType()  { return 'String'; }
    public function getLength()  { return 20; }
    public function getUnsigned()  { return False; }
    public function getNotNull()  { return True; }
}

class SchemaCol2Stub
{
    public function getName()  { return 'col2'; }
    public function getType()  { return 'Integer'; }
    public function getLength()  { return Null; }
    public function getUnsigned()  { return True; }
    public function getNotNull()  { return False; }
}

class SchemaColCodeStub
{
    public function getName()  { return 'code'; }
    public function getType()  { return 'String'; }
    public function getLength()  { return 4; }
    public function getUnsigned()  { return False; }
    public function getNotNull()  { return True; }
}


class SchemaIndexStub
{
    public function getName() { return 'primary'; }
    public function getColumns() { return 'col1'; }
    public function isUnique() { return True; }
}

class SchemaIndexCol2Stub
{
    public function getName() { return 'col2_index'; }
    public function getColumns() { return 'col2'; }
    public function isUnique() { return False; }
}

class SchemaIndexCodeStub
{
    public function getName() { return 'code_index'; }
    public function getColumns() { return 'code'; }
    public function isUnique() { return True; }    
}

