<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 10/31/14
 * Time: 1:05 AM
 */

class DBPagesCacheType extends DBType
{

    protected $tableName = DB::TABLE_PAGES_CACHE___NAME;

    public function DBPagesCacheType()
    {
        $this->DBType();
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
        return DB::TABLE_PAGES_CACHE__ID;
    }

    protected function getOrder()
    {
        return DB::TABLE_PAGES_CACHE___ORDER;
    }

    public function getCache($pageUrl)
    {
        $hash = md5($pageUrl);
        $this->executeRequestWithLimit(DB::TABLE_PAGES_CACHE__HASH, $hash, $this->getOrder(), "asc", 0, 1);
        while ($row = mysqli_fetch_array($this->response)) {
            return html_entity_decode($row[DB::TABLE_PAGES_CACHE__CONTENT]);
        }
        return '';
    }

    public function setCache($pageUrl, $content)
    {
        $hash = md5($pageUrl);
        $escapedString = mysqli_escape_string($this->getConnection()->getLink(), htmlentities($content));
        $this->execute(
            "INSERT INTO ".$this->getTableName()."(`hash`, `content`) VALUES ('".$hash."','".$escapedString."') ON DUPLICATE KEY UPDATE `content` = '".$escapedString."';"
        );
    }
}