<?php

namespace Jijoel\ValidationRuleGenerator;

use InvalidArgumentException;
use Illuminate\Support\Facades\DB;

class Generator
{
    protected $schemaManager;

    public function __construct($schemaManager = null)
    {
        $this->schemaManager = $schemaManager ? :
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
        if ( ! $table == null && $column == null) {
            $return = $this->getTableRules($table, $rules);
            return ($id == null) ? $return : $this->getUniqueRules($return, $id);
        } elseif ( ! $table == null && ! $column == null ) {
            $return = $this->getColumnRules($table, $column, $rules);
            return ($id == null) ? $return : $this->getUniqueRules($return, $id);
        } elseif ($table == null && $column == null && $rules == null && $id == null) {
            return $this->getAllTableRules();
        }

        throw new InvalidArgumentException;
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

        $tables = $this->schemaManager->listTableNames();
        foreach($tables as $table){
            $rules[$table] = $this->getTableRules($table);
        }
        return $rules;
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
        $table = $this->getTableName($table);

        $tableRules = $this->getTableRuleArray($table);
        if (is_null($rules)) {
            return $this->implodeTableRules($tableRules);
        }

        $userRules = $this->explodeTableRules($rules);
        $merged = array_replace_recursive($tableRules, $userRules);
        return $this->implodeTableRules($merged);
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
        $table = $this->getTableName($table);
        $col = DB::connection()->getDoctrineColumn($table, $column);
        $dbRuleArray = $this->getColumnRuleArray($col);
        $indexRuleArray = $this->getIndexRuleArray($table, $column);
        $merged = array_merge($dbRuleArray, $indexRuleArray);

        if (is_null($rules)) {
            return $this->implodeColumnRules($merged);
        }

        $userRuleArray = $this->explodeColumnRules($rules);
        $merged = array_merge($merged, $userRuleArray);
        return $this->implodeColumnRules($merged);
    }


    public function getUniqueRules($rules, $id, $idColumn='id')
    {
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
     * Explode a given set of rules for a given table into an associative array of rules.
     *
     * @param  string|array  $rules
     * @return array
     */
    protected function explodeTableRules($rules)
    {
        $ruleArray = array();
        foreach($rules as $field => $value) {
            $ruleArray[$field] = $this->explodeColumnRules($value);
        }
        return $ruleArray;
    }

    /**
     * Split rules for a single field into an array
     *
     * @param  string|array $rules  Rules in the format 'rule:attribute|rule|rule'
     * @return array                Associative array of all rules
     */
    protected function explodeColumnRules($rules)
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
    protected function implodeTableRules($ruleArray)
    {
        $rules = array();
        foreach($ruleArray as $column => $data) {
            $rules[$column] = $this->implodeColumnRules($data);
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
    protected function implodeColumnRules($ruleArray)
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
        if ($col->getNotnull()) {
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

    protected function getTableName($table)
    {
        if (is_string($table))
            return $table;

        if (is_object($table) && method_exists($table, 'getTable'))
            return $table->getTable();

        throw new InvalidArgumentException;
    }
}

