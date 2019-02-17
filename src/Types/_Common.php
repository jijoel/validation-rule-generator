<?php

namespace Jijoel\ValidationRuleGenerator\Types;


trait _Common
{

    protected function setCol($col)
    {
        $this->col = $col;
        $this->skipAutomaticFields();
    }


    protected function skipAutomaticFields()
    {
        $skip = [
            'id', 'created_at', 'updated_at', 'deleted_at'
        ];

        $name = $this->col->getName();

        if (in_array($name, $skip))
            throw new SkipThisColumn;
    }

    protected function nullable()
    {
        if ($this->col->getNotNull())
            return $this->rules['required'] = null;

        return $this->rules['nullable'] = null;
    }
}
