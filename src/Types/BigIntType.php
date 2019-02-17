<?php

namespace Jijoel\ValidationRuleGenerator\Types;


class BigIntType
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
        $this->unsignedMin();

        return $this->rules;
    }

    protected function unsignedMin()
    {
        if ($this->col->getUnsigned())
            $this->rules['min'] = 0;
    }

}
