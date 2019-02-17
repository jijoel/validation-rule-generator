<?php

namespace Jijoel\ValidationRuleGenerator\Types;


class TimeType
{
    use _Common;
    use _Dates;

    public $col;
    public $rules = [];

    public function __invoke($col)
    {
        $this->setCol($col);

        $this->nullable();
        $this->date();

        return $this->rules;
    }

}
