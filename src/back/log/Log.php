<?php
include_once("import");

class Log {
    public static function db($message) {
        error_log(date("Y-m-d H:i:s")." ".LogLevel::INFO." ".$message."\n", 3, "logs/DB_Connections.log");
    }

    public static function info($message) {
        error_log(date("Y-m-d H:i:s")." ".LogLevel::INFO." ".$message."\n", 3, "logs/logs.log");
    }

    public static function temp($message) {
        error_log(date("Y-m-d H:i:s")." ".LogLevel::INFO." ".$message."\n", 3, "logs/temp.log");
    }

}