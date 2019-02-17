<?php

namespace Jijoel\ValidationRuleGenerator;

use InvalidArgumentException;
use Illuminate\Support\Facades\DB;

class Schema
{
    protected $schemaManager;
    protected $indexes;

    public function __construct($schemaManager = null)
    {
        $this->schemaManager = $schemaManager ?:
            DB::connection()->getDoctrineSchemaManager();
    }

    public function tables()
    {
        return $this->schemaManager->listTableNames();
    }

    public function columns($table)
    {
        return $this->schemaManager->listTableColumns($table);
    }

    public function columnData($table, $column)
    {
        return DB::connection()->getDoctrineColumn($table, $column);
    }

    public function indexes($table)
    {
        if (isset($this->indexes[$table]))
            return $this->indexes[$table];

        return $this->indexes[$table] = $this->schemaManager->listTableIndexes($table);
    }
}
