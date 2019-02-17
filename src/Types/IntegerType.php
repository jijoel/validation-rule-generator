<?php

namespace Jijoel\ValidationRuleGenerator\Types;


class IntegerType
{
    public $col;

    public function __invoke($col)
    {
        $colArray = [];

        // $colArray['integer']=null;
        if ($col->getUnsigned()) {
            $colArray['min'] = '0';
        }

        if ($col->getNotNull()) {
            $colArray['required']=null;
        }

        return $colArray;
    }
}
