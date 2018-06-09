<?php

include_once AuWebRoot.'/src/back/import/import.php';
include_once AuWebRoot.'/src/back/import/db.php';
include_once AuWebRoot.'/src/back/import/services.php';

class AuthManager {

    public static function makeUserAuth($userName, $userPassword) {

    }

    public static function destroySession($sessionId) {

    }

    public static function checkUser($userName) {
        $userDBType = new DBUsersType();
        $user = $userDBType->getUserForName($userName);
        $result = strlen($user[DB::TABLE_USERS__NAME]) > 0;
        return $result;
    }

    public static function checkUserPasswd($userName, $passwd) {
        $result = false;
        $userDBType = new DBUsersType();
        $user = $userDBType->getUserForName($userName);
        if (!is_null($user)) {
            $dbPasswd = $user[DB::TABLE_USERS__PASSWORD];
            $webPasswd = md5($passwd);
            if ($dbPasswd == $webPasswd) {
                $result = true;
            }
        }
        return $result;
    }

    public static function makeAuth() {
        $result = false;
        $POST = Utils::getPostSource();
        if (array_key_exists(Constants::LOGIN_USER, $POST) &&
            array_key_exists(Constants::LOGIN_PASSWORD, $POST)) {
            $userName = Utils::getFromPOST(Constants::LOGIN_USER, false);
            $passwd = Utils::getFromPOST(Constants::LOGIN_PASSWORD, false);
            $isUserExist = self::checkUserPasswd($userName, $passwd);
            if ($isUserExist) {
                $_SESSION[Constants::LOGIN_USER]= $userName;
                $result = true;
            }
        }
        return $result;
    }

    public static function isAuth() {
        return array_key_exists(Constants::LOGIN_USER, $_SESSION) && self::checkUser($_SESSION[Constants::LOGIN_USER]);
    }

}