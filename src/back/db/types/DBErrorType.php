<?php

include_once AuWebRoot.'/src/back/import/db.php';

class DBErrorType extends DBType
{
    protected $tableName = DB::TABLE_ERRORS___NAME;

    public function __construct()
    {
        parent::__construct();
        return $this;
    }

    protected function getTable()
    {
        return $this->tableName;
    }

    protected function getTableName()
    {
        return $this->tableName;
    }

    protected function getIndexColumn()
    {
        return DB::TABLE_ERRORS__ID;
    }

    protected function getOrder()
    {
        return DB::TABLE_ERRORS___ORDER;
    }

    public function saveExceptionFromParams($name, $message, $stack = '')
    {
        $this->update(null,
            [
                DB::TABLE_ERRORS__NAME => $name,
                DB::TABLE_ERRORS__STACK => $stack,
                DB::TABLE_ERRORS__DATE => date('Y-m-d H:i:s'),
                DB::TABLE_ERRORS__MESSAGE => $message
            ]
        );
    }

    public function createException($error)
    {
        if (is_array($error)) {
            $this->update(null,
                [
                    DB::TABLE_ERRORS__NAME => $error['name'],
                    DB::TABLE_ERRORS__STACK => $error['stack'],
                    DB::TABLE_ERRORS__DATE => $error['date'],
                    DB::TABLE_ERRORS__MESSAGE => $error['message']
                ]
            );
            return;
        }
        $this->update(null,
            [
                DB::TABLE_ERRORS__NAME => get_class($error),
                DB::TABLE_ERRORS__STACK => $error->getTraceAsString(),
                DB::TABLE_ERRORS__DATE => date("Y-m-d H:i:s"),
                DB::TABLE_ERRORS__MESSAGE => $error->getMessage()
            ]
        );
    }

}