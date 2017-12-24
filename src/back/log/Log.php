<?php
include_once("src/back/import/import");
include_once("src/back/import/errors");

class Log {
    public static function db($message) {
        return;
        self::prepare('logs', 'logs/DB_Connections.log');
        error_log(date("Y-m-d H:i:s")." ".LogLevel::INFO." ".$message."\n", 3, "logs/DB_Connections.log");
    }

    public static function info($message) {
        return;
        self::prepare('logs', 'logs/logs.log');
        error_log(date("Y-m-d H:i:s")." ".LogLevel::INFO." ".$message."\n", 3, "logs/logs.log");
    }

    public static function temp($message) {
        return;
        self::prepare('logs', 'logs/temp.log');
        //error_log(date("Y-m-d H:i:s")." ".LogLevel::INFO." ".$message."\n", 3, "logs/temp.log");
    }

    private static function prepare($dir, $filePath) {
        if (!file_exists($filePath)) {
            if (!is_dir($dir)) {
                $result = mkdir($dir, '0777', true);
                if ($result == false) {
                    $error = error_get_last();
                    throw new InternalError("Creating directory '$dir' failed: " . $error['message']);
                }
            }
            $result = fopen($filePath, 'rw');
            if ($result == false) {
                $error = error_get_last();
                throw new InternalError("Creating file '$filePath' failed: ".$error['message']);
            }
        }
    }

}