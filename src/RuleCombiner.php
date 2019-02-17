<?php

namespace Jijoel\ValidationRuleGenerator;

class RuleCombiner
{
    public function tables($set1, $set2)
    {
        return $this->mergeRules($set1, $set2, 'Table');
    }

    public function columns($set1, $set2)
    {
        return $this->mergeRules($set1, $set2, 'Column');
    }

    private function mergeRules($set1, $set2, $type)
    {
        $splitter = "split{$type}";
        $joiner = "join{$type}";

        if (!$set2 || !count($set2))
            return $this->$joiner($set1);

        if (!$set1 || !count($set1))
            return $this->$joiner($set2);

        $set1Array = $this->$splitter($set1);
        $set2Array = $this->$splitter($set2);
        $merged = array_replace_recursive($set1Array, $set2Array);

        return $this->$joiner($merged);
    }

    /**
     * Split a given set of rules into an associative array.
     *
     * @param  string|array  $rules
     * @return array
     */
    private function splitTable($rules)
    {
        $ruleArray = [];

        foreach($rules as $field => $value) {
            $ruleArray[$field] = $this->splitColumn($value);
        }

        return $ruleArray;
    }

    /**
     * Split rules for a single field into an array
     *
     * @param  string|array $rules  Rules in the format 'rule:attribute|rule|rule'
     * @return array                Associative array of all rules
     */
    private function splitColumn($rules)
    {
        $columnRules = [];
        $columnRuleArray = is_string($rules)
            ? explode('|', $rules)
            : $rules;

        foreach ($columnRuleArray as $columnRule) {
            list($key, $param) = $this->parse($columnRule);
            $columnRules[$key] = $param;
        }

        return $columnRules;
    }

    /**
     * Parse an individual rule, in the form:
     * 'rule:attribute', or 'rule'
     *
     * @param  string  $rule
     * @return array            array of [rule => attribute]
     */
    private function parse($rule)
    {
        $attribute = null;

        if (strpos($rule, ':') !== false)
            list($rule, $attribute) = explode(':', $rule, 2);

        return array($rule, $attribute);
    }

    /**
     * Given a nested array of columns and individual rules,
     * return an array of columns with a delimited string of rules.
     *
     * @param  array $ruleArray
     * @return array
     */
    private function joinTable($ruleArray)
    {
        $rules = [];

        foreach($ruleArray as $column => $data) {
            $rules[$column] = $this->joinColumn($data);
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
    private function joinColumn($ruleArray)
    {
        $rules = '';

        foreach($ruleArray as $key => $value) {
            if (is_null($value)) {
                $rules .= $key .'|';
            } else {
                $rules .= $key .':'. $value . '|';
            }
        }

        return substr($rules,0,-1);
    }

}
