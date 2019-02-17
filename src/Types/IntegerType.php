<?php

namespace Jijoel\ValidationRuleGenerator\Types;


class IntegerType
{
    use _Common;
    use _Numeric;

    public $col;
    public $rules = [];

    public function __invoke($col)
    {
        $this->setCol($col);

        $this->nullable();
        $this->integer();
        $this->min(-2147483648);
        $this->max(2147483647, 4294967295);

        return $this->rules;
    }

}
