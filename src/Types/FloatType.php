<?php

namespace Jijoel\ValidationRuleGenerator\Types;


class FloatType
{
    use _Common;
    use _Numeric;

    public $col;
    public $rules = [];

    public function __invoke($col)
    {
        $this->setCol($col);

        $this->nullable();
        $this->numeric();

        return $this->rules;
    }

}
