<?php

namespace Jijoel\ValidationRuleGenerator\Types;


class StringType
{
    public function __invoke($col)
    {
        $colArray = [];

        if( $len = $col->getLength() ) {
            $colArray['max'] = $len;
        }

        if ($col->getNotNull()) {
            $colArray['required']=null;
        }

        return $colArray;

    }
}
