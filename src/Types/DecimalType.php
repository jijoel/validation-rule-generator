<?php

namespace Jijoel\ValidationRuleGenerator\Types;


class DecimalType
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
        $this->getMax();

        return $this->rules;
    }

    protected function getMax()
    {
        $precision = $this->col->getPrecision();
        $scale = $this->col->getScale();

        $decimal = str_repeat('9', $scale);
        $whole = str_repeat('9', $precision - $scale);

        $this->rules['max'] = ($whole.'.'.$decimal);
    }

}
