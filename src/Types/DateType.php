<?php

namespace Jijoel\ValidationRuleGenerator\Types;


class DateType
{
    use _Common;
    use _Dates;

    public $col;
    public $rules = [];

    public function __invoke($col)
    {
        $this->col = $col;

        $this->nullable();
        $this->date();

        return $this->rules;
    }

}
