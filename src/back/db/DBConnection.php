<?php
include_once("import");
include_once("db");
class DBConnection{
    private $request;
    public $affectedRows;
    public $insertId;
    public $response;
    public $totalCount = 0;
    public $responseSize = 0;

    public function DBConnection() {
    }

    private function getInstanceApache2() {
        mysql_connect(DB::APACHE2_MYSQL_HOST_NAME, DB::APACHE2_MYSQL_USER_NAME, DB::APACHE2_MYSQL_PASS) OR DIE("Cannot connect! ".mysql_error());
        mysql_set_charset('utf8');
        mysql_select_db(DB::APACHE2_MYSQL_DATA_BASE_NAME) or die(mysql_error());
    }

    private function getInstanceLocApache2() {
        mysql_connect(DB::LOC_APACHE2_MYSQL_HOST_NAME, DB::LOC_APACHE2_MYSQL_USER_NAME, DB::LOC_APACHE2_MYSQL_PASS) OR DIE("Cannot connect! ".mysql_error());
        mysql_set_charset('utf8');
        mysql_select_db(DB::LOC_APACHE2_MYSQL_DATA_BASE_NAME) or die(mysql_error());
    }

    public function execute($sqlCommand) {
        //self::getInstanceLocApache2();
        $this->getInstanceApache2();
        $this->response = mysql_query($sqlCommand);
        $this->insertId = mysql_insert_id();
        $this->affectedRows = mysql_affected_rows();
        if (false === $this->response) {
            echo mysql_error();
        }
        mysql_close();
        return $this->response;
    }
}
?>
