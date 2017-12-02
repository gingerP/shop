<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 3/7/15
 * Time: 1:40 AM
 */

include_once("src/back/import/import");
include_once("src/back/import/db");
include_once("src/back/import/service");

class AuthManager {

    public static function makeUserAuth($userName, $userPassword) {

    }

    public static function destroySession($sessionId) {

    }

    public static function checkUser($user) {
        $userDBType = new DBUsersType();
        $userArr = $userDBType->getUserForName($user);
        $result = count($userArr) > 0 && strlen($userArr[DB::TABLE_USERS__NAME]) > 0;
        return $result;
    }

    public static function checkUserPasswd($user, $passwd) {
        $result = false;
        $userDBType = new DBUsersType();
        $userArr = $userDBType->getUserForName($user);
        if (count($userArr) > 0) {
            $dbPasswd = $userArr[DB::TABLE_USERS__PASSWORD];
            $webPasswd = md5($passwd);
            if ($dbPasswd == $webPasswd) {
                $result = true;
            }
        }
        return $result;
    }

    public static function makeAuth() {
        $result = false;
        if (array_key_exists(Constants::LOGIN_USER, $_POST) &&
            array_key_exists(Constants::LOGIN_PASSWORD, $_POST)) {
            $userName = $_POST[Constants::LOGIN_USER];
            $passwd = $_POST[Constants::LOGIN_PASSWORD];
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