<?php

namespace Jijoel\ValidationRuleGenerator;

use InvalidArgumentException;
use Illuminate\Support\Facades\DB;
// use Illuminate\Console\DetectsApplicationNamespace;

class Generator
{
    // use DetectsApplicationNamespace;

    protected $schemaManager;

    public function __construct($schemaManager = null)
    {
        $this->schemaManager = $schemaManager ?:
            DB::connection()->getDoctrineSchemaManager();
    }

    /**
     * Returns rules for the selected object
     *
     * @param  string|object $table  The table or model for which to get rules
     * @param  string $column        The column for which to get rules
     * @param  array $rules          Manual overrides for the automatically-generated rules
     * @param  integer $id           An id number to ignore for unique fields (current id)
     *
     * @return array                 Array of calculated rules for the given table/model
     */
    public function getRules($table=null, $column=null, $rules=null, $id=null)
    {
        if (is_null($table) && ! is_null($column))
            throw new InvalidArgumentException;

        if (is_object($table))
            return $this->getUniqueRules($this->getModelRules($table, $rules, $column), $id);

        if (is_null($table))
            return $this->getAllTableRules();

        if (is_null($column))
            return $this->getUniqueRules($this->getTableRules($table, $rules), $id);

        return $this->getUniqueRules($this->getColumnRules($table, $column, $rules), $id);
    }

    public static function make()
    {
        return new static();
    }



    /**
     * Return the DB-specific rules from all tables in the database
     * (this does not contain any user-overrides)
     *
     * @return array   An associative array of columns and delimited string of rules
     */
    public function getAllTableRules()
    {
        $rules = array();

        $tables = $this->getTableNames();
        foreach($tables as $table){
            $rules[$table] = $this->getTableRules($table);
        }
        return $rules;
    }

    private function getTableNames()
    {
        return $this->schemaManager->listTableNames();
    }

    public function getModelRules($model, $rules=[], $column=null)
    {
        $instance = $this->getModelInstance($model);
        // $namespace = $this->getAppNamespace();

        $table = $instance->getTable();
        $_rules = $this->combineRules($instance::$rules ?? [], $rules);

        return ($column)
            ? $this->getColumnRules($table, $column, $_rules)
            : $this->getTableRules($table, $_rules);
    }

    private function getModelInstance($model)
    {
        if (is_object($model))  return $model;

        // $namespace = $this->getAppNamespace();

        $modelClass = str_replace('/', '\\', $model);

        return new $modelClass;
    }

    private function combineRules($set1, $set2)
    {
        if (!$set2 || !count($set2))  return $set1;
        if (!$set1 || !count($set1))  return $set2;

        $set1Array = $this->splitTableRules($set1);
        $set2Array = $this->splitTableRules($set2);
        $merged = array_replace_recursive($set1Array, $set2Array);

        return $this->joinTableRules($merged);
    }

    /**
     * Returns all rules for a given table
     *
     * @param  string|object $table  The name of the table to analyze
     * @param  array  $rules    (optional) Additional (user) rules to include
     *                          These will override the automatically generated rules
     * @return array  An associative array of columns and delimited string of rules
     */
    public function getTableRules($table, $rules = null)
    {
        $tableRules = $this->getTableRuleArray($table);
        if (is_null($rules)) {
            return $this->joinTableRules($tableRules);
        }

        $userRules = $this->splitTableRules($rules);
        $merged = array_replace_recursive($tableRules, $userRules);
        return $this->joinTableRules($merged);
    }

    /**
     * Returns all of the rules for a given table/column
     *
     * @param  string $table    Name of the table which contains the column
     * @param  string $column   Name of the column for which to get rules
     * @param  string|array $rules    Additional information or overrides.
     * @return string           The final calculated rules for this column
     */
    public function getColumnRules($table, $column, $rules = null)
    {
        // TODO: Work with foreign keys for exists:table,column statements

        // Get an array of rules based on column data
        $col = DB::connection()->getDoctrineColumn($table, $column);
        $dbRuleArray = $this->getColumnRuleArray($col);
        $indexRuleArray = $this->getIndexRuleArray($table, $column);
        $merged = array_merge($dbRuleArray, $indexRuleArray);

        if (is_null($rules)) {
            return $this->joinColumnRules($merged);
        }

        $userRuleArray = $this->splitColumnRules($rules);
        $merged = array_merge($merged, $userRuleArray);
        return $this->joinColumnRules($merged);
    }


    public function getUniqueRules($rules, $id, $idColumn='id')
    {
        if (is_null($id))
            return $rules;

        if (! is_array($rules)) {
            return $this->getColumnUniqueRules($rules, $id, $idColumn);
        }

        $return = array();
        foreach ($rules as $key => $value) {
            $return[$key] = $this->getColumnUniqueRules($value, $id, $idColumn);
        }
        return $return;
    }

    /**
     * Given a set of rules, and an id for a current record,
     * returns a string with any unique rules skipping the current record.
     *
     * @param  string         $rules    A laravel rule string
     * @param  string|integer $id       The id to skip
     * @param  string         $idColumn The name of the column
     *
     * @return string The rules, including a string to skip the given id.
     */
    public function getColumnUniqueRules($rules, $id, $idColumn='id')
    {
        $upos = strpos($rules, 'unique:');
        if ($upos === False) {
            return $rules;      // no unique rules for this field; return
        }

        $pos = strpos($rules, '|', $upos);
        if ($pos === False) {       // 'unique' is the last rule; append the id
            return $rules . ',' . $id . ',' . $idColumn;
        }

        // inject the id
        return substr($rules, 0, $pos) . ',' . $id . ',' . $idColumn . substr($rules, $pos);
    }

    /**
     * Split a given set of rules into an associative array.
     *
     * @param  string|array  $rules
     * @return array
     */
    protected function splitTableRules($rules)
    {
        $ruleArray = array();
        foreach($rules as $field => $value) {
            $ruleArray[$field] = $this->splitColumnRules($value);
        }
        return $ruleArray;
    }

    /**
     * Split rules for a single field into an array
     *
     * @param  string|array $rules  Rules in the format 'rule:attribute|rule|rule'
     * @return array                Associative array of all rules
     */
    protected function splitColumnRules($rules)
    {
        $columnRules = array();
        $columnRuleArray = is_string($rules) ? explode('|', $rules) : $rules;
        foreach ($columnRuleArray as $columnRule) {
            list($key, $param) = $this->parseRule($columnRule);
            $columnRules[$key] = $param;
        }
        return $columnRules;
    }

    /**
     * Given a nested array of columns and individual rules,
     * return an array of columns with a delimited string of rules.
     *
     * @param  array $ruleArray
     * @return array
     */
    protected function joinTableRules($ruleArray)
    {
        $rules = array();
        foreach($ruleArray as $column => $data) {
            $rules[$column] = $this->joinColumnRules($data);
        }
        return $rules;
    }

    /**
     * Given an array of individual rules (eg, ['min'=>2,'exists'=>'countries,code','unique'],
     * return a string delimited by a pipe (eg: 'min:2|exists:countries,code|unique')
     *
     * @param  array $ruleArray
     * @return string
     */
    protected function joinColumnRules($ruleArray)
    {
        $rules = '';
        foreach($ruleArray as $key => $value) {
            if($value!==null) {
                $rules .= $key .':'. $value . '|';
            } else {
                $rules .= $key .'|';
            }
        }
        return substr($rules,0,-1);
    }

    /**
     * Parse an individual rule, in the form:
     * 'rule:attribute', or 'rule'
     *
     * @param  string  $rule
     * @return array            array of [rule => attribute]
     */
    protected function parseRule($rule)
    {
        $attribute = null;
        if (strpos($rule, ':') !== false)
        {
            list($rule, $attribute) = explode(':', $rule, 2);
        }

        return array($rule, $attribute);
    }

    /**
     * Returns an array of rules for a given database table
     *
     * @param  string $table    Name of the table for which to get rules
     * @return array            rules in a nested associative array
     */
    protected function getTableRuleArray($table)
    {
        if (! is_string($table))
            throw new InvalidArgumentException;

        $rules = [];
        $columns = $this->schemaManager->listTableColumns($table);

        foreach($columns as $column) {
            $colName = $column->getName();

            // Add generated rules from the database for this column, if any found
            $columnRules = $this->getColumnRuleArray($column);
            if ($columnRules) {
                $rules[$colName] = $this->getColumnRuleArray($column);
            }

            // Add index rules for this column, if any are found
            $indexRules = $this->getIndexRuleArray($table, $colName);
            if ($indexRules) {
                $rules[$colName] = array_merge($rules[$colName], $indexRules);
            }
        }

        return $rules;
    }

    /**
     * Returns an array of rules for a given database column, based on field information
     *
     * @param  Doctrine\DBAL\Schema\Column $col     A database column object (from Doctrine)
     * @return array                                An array of rules for this column
     */
    protected function getColumnRuleArray($col)
    {
        $colArray = array();
        $type = $col->getType();
        if ($type=='String') {
            if( $len = $col->getLength() ) {
                $colArray['max'] = $len;
            }
        } elseif ($type=='Integer') {
            $colArray['integer']=null;
            if ($col->getUnsigned()) {
                $colArray['min'] = '0';
            }
        }
        if ($col->getNotNull()) {
            $colArray['required']=null;
        }
        return $colArray;
    }

    /**
     * Determine whether a given column should include a 'unique' flag
     *
     * @param  string $table
     * @param  string $column
     * @return array
     */
    protected function getIndexRuleArray($table, $column)
    {
        // TODO: (maybe) Handle rules for indexes that span multiple columns
        $indexArray = array();
        $indexList = $this->schemaManager->listTableIndexes($table);
        foreach($indexList as $item) {
            $cols = $item->getColumns();
            if(in_array($column, $cols) !== false && count($cols)==1 && $item->isUnique()) {

                $indexArray['unique'] = $table . ',' . $column;
            }
        }
        return $indexArray;
    }

}

