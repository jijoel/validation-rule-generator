<?php

use PHPUnit\Framework\TestCase;

use Illuminate\Support\Facades\DB;
use Jijoel\ValidationRuleGenerator\Generator;



class ValidationRuleGeneratorTest extends TestCase
{
    use Helpers;

    /**
     * Test to make sure we can pass in a schema manager
     */
    public function testShouldGetSchemaManager()
    {
        $test = new Generator(Null);
        $result = print_r($test, true);
        $this->assertStringContainsString('SchemaManagerStub Object', $result);

        $test = new Generator('foo');
        $result = print_r($test, true);
        $this->assertStringNotContainsString('SchemaManagerStub Object', $result);
        $this->assertStringContainsString('foo', $result);
    }

    public function testGetColumnRules()
    {
        $codeRules = $this->columnRules('code');
        $this->assertStringContainsString('max:4', $codeRules);
        $this->assertStringContainsString('required', $codeRules);
        $this->assertStringContainsString('unique:foo,code', $codeRules);
    }

    public function testGetColumnRulesIncludesUnique()
    {
        $this->assertStringContainsString('unique',    $this->columnRules('code'));
        $this->assertStringContainsString('unique',    $this->columnRules('col1'));
        $this->assertStringNotContainsString('unique', $this->columnRules('col2'));
    }

    public function testUniqueIdsForColumn()
    {
        $rules = $this->ruleGenerator->getColumnRules('foo', 'code', Null);
        $test = $this->ruleGenerator->getColumnUniqueRules($rules, 12);
        $this->assertStringContainsString('unique:foo,code,12,id', $test);

        $test = $this->ruleGenerator->getColumnUniqueRules($rules, 17, 'buzz');
        $this->assertStringContainsString('unique:foo,code,17,buzz', $test);

        $test = $this->ruleGenerator->getUniqueRules($rules, 14);
        $this->assertStringContainsString('unique:foo,code,14,id', $test);

        $rules = 'foo:bazz|unique:fizz,bazz|buzz';
        $test = $this->ruleGenerator->getUniqueRules($rules, 12);
        $this->assertEquals('foo:bazz|unique:fizz,bazz,12,id|buzz', $test);
    }


    public function testGetColumnRulesIncludesRequired()
    {
        $this->assertStringContainsString('required',    $this->columnRules('code'));
        $this->assertStringContainsString('required',    $this->columnRules('col1'));
        $this->assertStringNotContainsString('required', $this->columnRules('col2'));
    }

    public function testGetColumnRulesIncludesMaxOnlyIfFound()
    {
        $this->assertStringContainsString('max:', $this->columnRules('col1'));
        // Deprecated: Return min and max for all values
        // $this->assertStringNotContainsString('max:', $this->columnRules('col2'));
    }

    public function testGetColumnRulesIncludesPassedInRule()
    {
        $r = $this->columnRules('code', array('bazz'));
        $this->assertStringContainsString('bazz', $r);
    }

    public function testGetColumnRulesCanBeOverridden()
    {
        $this->assertStringContainsString('max:4',$this->columnRules('code'));
        $this->assertStringContainsString('max:2',$this->columnRules('code', array('max:2')));
        $this->assertStringContainsString('unique:fizz,buzz',
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
        $this->assertStringContainsString('unique', $tableRules['code']);
        $this->assertStringContainsString('unique', $tableRules['col1']);
        $this->assertStringNotContainsString('unique', $tableRules['col2']);
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
        $this->assertStringContainsString('unique:foo,code', $r1['code']);
        $this->assertStringNotContainsString('unique:foo,code', $r2['code']);
        $this->assertStringContainsString('unique:fizz,buzz', $r2['code']);
    }

    public function testUniqueIdsForTable()
    {
        $rules = $this->ruleGenerator->getTableRules('foo', Null);
        $test = $this->ruleGenerator->getUniqueRules($rules, 12);
        $this->assertStringContainsString('unique:foo,code,12,id', $test['code']);
        $test = $this->ruleGenerator->getUniqueRules($rules, 14, 'bazz');
        $this->assertStringContainsString('unique:foo,code,14,bazz', $test['code']);
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
        $this->assertStringContainsString('max:12', $test['code']);
    }

    public function testGetRulesCanIncludeId()
    {
        $test = $this->ruleGenerator->getRules('table', Null, Null, 15);
        $this->assertStringContainsString('15', $test['code']);
    }

    public function testGetRulesWorksForModels()
    {
        $model = new ModelStub();

        $this->assertEquals($this->ruleGenerator->getTableRules('foo'),
            $this->ruleGenerator->getRules($model));

        $this->assertNotEquals($this->ruleGenerator->getTableRules('table'),
            $this->ruleGenerator->getRules($model));

        $this->assertEquals($this->ruleGenerator->getRules('foo', 'col1'),
            $this->ruleGenerator->getRules($model, 'col1'));
    }

    public function testGetRulesFailsWhenGivenBadValue()
    {
        $this->expectException(InvalidArgumentException::class);
        $foo = $this->ruleGenerator->getTableRules(1);
    }

    public function testSendBadDataToGetRules()
    {
        $this->expectException(InvalidArgumentException::class);
        $test = $this->ruleGenerator->getRules(Null, 'col');
    }

    public function testStaticMake()
    {
        // Generator::make();
        $test = $this->ruleGenerator;
        $t2   = $this->ruleGenerator->make();
        $this->assertEquals($test, $t2);
    }

}


