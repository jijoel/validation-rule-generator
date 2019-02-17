<?php

namespace Jijoel\ValidationRuleGenerator\Types;


class SmallIntType
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
        $this->min(-32768);
        $this->max(32767, 65535);

        return $this->rules;
    }

}
