<?php

// use Illuminate\Support\Facades\DB;
// use Kalani\SchemaInterface\ValidationRuleGenerator;
// use Doctrine\DBAL\Schema\Column;
// use Mockery as m;

/**
 * This class currently does not work. I'm not sure why. :-(
 * TODO: Figure out how to test packages
 */
// class ApiValidationRuleGeneratorTest extends PHPUnit_Framework_TestCase
class ApiValidationRuleGeneratorTest extends Orchestra\Testbench\TestCase
{
    private $ruleGenerator;
    private $defaultConnection;
    private $testTableCreator;

    public function setUp()
    {
        $this->markTestIncomplete();
        $this->defaultConnection = DB::connection()->getName();
        DB::setDefaultConnection('test');
        $this->testTableCreator = new CreateTestTable;
        $this->ruleGenerator = new ApiValidationRuleGenerator;
    }   

    public function tearDown()
    {
        DB::setDefaultConnection($this->defaultConnection);
    }

    public function testCreateDB()
    {
        $this->assertEquals('test', DB::connection()->getName());
        $this->testTableCreator->up();
        $this->assertTrue(Schema::hasTable('foo'));
    }

    // foo: id, oid, code, text1, text2
    public function testGetColumnRulesFromDB()
    {
        $codeRules = $this->columnRules('code');
        $this->assertContains('max:4', $codeRules);
        $this->assertContains('required', $codeRules);
        $this->assertContains('unique:foo,code', $codeRules);
    }

    public function testGetColumnRulesIncludesUnique()
    {
        $this->assertNotContains('unique', $this->columnRules('text1'));   
        $this->assertContains('unique',    $this->columnRules('code'));     
    }

    public function testGetColumnRulesIncludesRequired()
    {
        $this->assertContains('required',    $this->columnRules('text1'));   
        $this->assertNotContains('required', $this->columnRules('text2'));     
    }

    public function testGetColumnRulesIncludesMaxOnlyIfFound()
    {
        $this->assertContains('max:', $this->columnRules('text1'));

        DB::setDefaultConnection('sqlite');
        $ruleGenerator = new ApiValidationRuleGenerator;
        $this->testTableCreator->up();

        // text1 is also required, but that doesn't ever show up for sqlite
        $this->assertEmpty($this->columnRules('text1'));        
    }

    public function testGetColumnRulesIncludesPassedInRule()
    {
        DB::setDefaultConnection('sqlite');
        $ruleGenerator = new ApiValidationRuleGenerator;
        $this->testTableCreator->up();

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
        $this->assertEquals(5, count($tableRules));
    }

    public function testGetTableRulesIncludesUnique()
    {
        $tableRules = $this->ruleGenerator->getTableRules('foo');
        $this->assertContains('unique', $tableRules['code']);
        $this->assertNotContains('unique', $tableRules['text2']);
    }

    public function testGetTableRulesMatchColumnRules()
    {
        $tableRules = $this->ruleGenerator->getTableRules('foo');
        $columnRules = $this->columnRules('code');
        $this->assertEquals($columnRules, $tableRules['code']);
    }

    public function testGetTableRulesIncludesPassedInRule()
    {
        DB::setDefaultConnection('sqlite');
        $ruleGenerator = new ApiValidationRuleGenerator;
        $this->testTableCreator->up();

        $r = $ruleGenerator->getTableRules('foo', array(
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
        // TODO: Figure out why 'unique' rule can not be overriden
        // $this->assertContains('unique:fizz,buzz', $r2['code']);
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