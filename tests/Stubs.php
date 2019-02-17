<?php

class DbConnectionStub
{
    public function getDoctrineSchemaManager() { return new SchemaManagerStub; }

    public function getDoctrineColumn($table, $col)
    {
        if ($col=='code')
            return new SchemaColCodeStub;
        elseif ($col == 'col1')
            return new SchemaCol1Stub;
        elseif ($col == 'col2')
            return new SchemaCol2Stub;

        throw new \InvalidArgumentException;
    }
}

class SchemaManagerStub
{
    public function listTableNames()
    {
        return array('foo', 'bar');
    }

    public function listTableColumns($table)
    {
        return array(
            'col1' => new SchemaCol1Stub,
            'col2' => new SchemaCol2Stub,
            'code' => new SchemaColCodeStub,
        );
    }

    public function listTableIndexes($table)
    {
        return array(
            'primary'=> new SchemaIndexStub,
            'code' => new SchemaIndexCodeStub,
            'col2_index'=> new SchemaIndexCol2Stub,
        );
    }
}

class SchemaCol1Stub
{
    public function getName()  { return 'col1'; }
    public function getType()  { return 'String'; }
    public function getLength()  { return 20; }
    public function getUnsigned()  { return False; }
    public function getNotNull()  { return True; }
}

class SchemaCol2Stub
{
    public function getName()  { return 'col2'; }
    public function getType()  { return 'Integer'; }
    public function getLength()  { return Null; }
    public function getUnsigned()  { return True; }
    public function getNotNull()  { return False; }
}

class SchemaColCodeStub
{
    public function getName()  { return 'code'; }
    public function getType()  { return 'String'; }
    public function getLength()  { return 4; }
    public function getUnsigned()  { return False; }
    public function getNotNull()  { return True; }
}


class SchemaIndexStub
{
    public function getName() { return 'primary'; }
    public function getColumns() { return array('col1'); }
    public function isUnique() { return True; }
}

class SchemaIndexCol2Stub
{
    public function getName() { return 'col2_index'; }
    public function getColumns() { return array('col2'); }
    public function isUnique() { return False; }
}

class SchemaIndexCodeStub
{
    public function getName() { return 'code_index'; }
    public function getColumns() { return array('code'); }
    public function isUnique() { return True; }
}

class ModelStub
{
    public function getTable() { return 'foo'; }
}
