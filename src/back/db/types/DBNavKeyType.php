<?php

include_once AuWebRoot . '/src/back/import/db.php';

class DBNavKeyType extends DBType
{

    public function __construct()
    {
        parent::__construct();
        $this->mappings = [
            DB::TABLE_NAV_KEY__ID => DB::TABLE_NAV_KEY__ID,
            DB::TABLE_NAV_KEY__KEY_ITEM => DB::TABLE_NAV_KEY__KEY_ITEM,
            DB::TABLE_NAV_KEY__PARENT_KEY => DB::TABLE_NAV_KEY__PARENT_KEY,
            DB::TABLE_NAV_KEY__VALUE => DB::TABLE_NAV_KEY__VALUE,
            DB::TABLE_NAV_KEY__ORDER => DB::TABLE_NAV_KEY__ORDER,
            DB::TABLE_NAV_KEY__IMAGE => DB::TABLE_NAV_KEY__IMAGE
        ];

    }

    public function getNameByKey($key)
    {
        $this->executeRequest(DB::TABLE_NAV_KEY__KEY_ITEM, $key, DB::TABLE_ORDER, DB::ASC);
        if ($this->responseSize != 0) {
            $row = mysqli_fetch_array($this->response);
            return $row[DB::TABLE_NAV_KEY__VALUE];
        }
        return '';
    }

    public function getLeafs()
    {
        $sqlCommand = "SELECT DISTINCT nk.* FROM nav_key nk, goods g  WHERE nk.key_item=SUBSTRING(g.key_item, 1, 2)";
        $navKeys = new DBNavKeyType();
        return $navKeys->execute($sqlCommand);
    }

    protected function getTable()
    {
        return DB::TABLE_NAV_KEY___NAME;
    }

    protected function getTableName()
    {
        return DB::TABLE_NAV_KEY___NAME;
    }

    protected function getIndexColumn()
    {
        return DB::TABLE_NAV_KEY__ID;
    }

    protected function getOrder()
    {
        return DB::TABLE_NAV_KEY___ORDER;
    }
}