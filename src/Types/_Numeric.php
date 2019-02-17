<?php

namespace Jijoel\ValidationRuleGenerator\Types;


trait _Numeric
{
    protected function numeric()
    {
        $this->rules['numeric'] = null;
    }

    protected function integer()
    {
        $this->rules['integer'] = null;
    }

    protected function min($signed, $unsigned=0)
    {
        if ($this->col->getUnsigned())
            return $this->rules['min'] = $unsigned;

        $this->rules['min'] = $signed;
    }

    protected function max($signed, $unsigned)
    {
        if ($this->col->getUnsigned())
            return $this->rules['max'] = $unsigned;

        $this->rules['max'] = $signed;
    }

}



