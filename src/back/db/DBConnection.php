<?php
include_once AuWebRoot.'/src/back/import/import.php';
include_once AuWebRoot.'/src/back/import/db.php';

class DBConnection
{
    private $request;
    public $affectedRows;
    public $insertId;
    public $response;
    public $totalCount = 0;
    public $responseSize = 0;
    public $link;

    public function __construct()
    {
    }

    private function getInstance()
    {
        $config = $GLOBALS['config'];
        $this->link = mysqli_connect(
            $config['db.host'],
            $config['db.user_name'],
            $config['db.password'],
            $config['db.database_name']
        );

        if ($this->link === false) {
            throw new Exception(mysqli_connect_errno().'. '.mysqli_connect_error());
        }
        mysqli_set_charset($this->link, 'utf8mb4');
    }

    public function execute($sqlCommand)
    {
        $this->getInstance();
        $this->response = mysqli_query($this->link, $sqlCommand);
        $this->insertId = mysqli_insert_id($this->link);
        $this->affectedRows = mysqli_affected_rows($this->link);
        return $this->response;
    }

    public function init()
    {
        $this->getInstance();
        return $this;
    }

    public function getLink()
    {
        return $this->link;
    }
}

?>
