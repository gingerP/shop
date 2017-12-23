<?php
class SessionManager {

    public static function sessionStart($isUserActivity=true) {
        if (session_id()) {
            return true;
        }
        ini_set('session.cookie_lifetime', 0);
        if (!session_start()) {
            return false;
        }
        $currentTime = time();

        //validate session
        if (isset($_SESSION[Constants::SESSION_USER_IP])) {
            if ($_SESSION[Constants::SESSION_USER_IP] != $_SERVER['REMOTE_ADDR']) {
                SessionManager::sessionDestroy();
                return false;
            }
        } else {
            $_SESSION[Constants::SESSION_USER_IP] = $_SERVER['REMOTE_ADDR'];
        }
        if (Constants::SESSION_LIFE_TIME) {
            if (isset($_SESSION[Constants::SESSION_LAST_ACTIVITY]) && $currentTime - $_SESSION[Constants::SESSION_LAST_ACTIVITY] >= Constants::SESSION_LIFE_TIME) {
                SessionManager::sessionDestroy();
                return false;
            } else if ($isUserActivity) {
                $_SESSION[Constants::SESSION_LAST_ACTIVITY] = $currentTime;
            }
        }
/*        if (Constants::SESSION_ID_LIFETIME) {
            if (isset($_SESSION[Constants::SESSION_START_TIME])) {
                if ($currentTime - $_SESSION[Constants::SESSION_START_TIME] >= Constants::SESSION_ID_LIFETIME) {
                    session_regenerate_id(true);
                    $_SESSION[Constants::SESSION_START_TIME] = $currentTime;
                }
            } else {
                $_SESSION[Constants::SESSION_START_TIME] = $currentTime;
            }
        }*/
        return true;
    }

    public static function  sessionDestroy() {
        if (session_id()) {
            session_unset();
            setcookie(session_name(), session_id(), time() - 60*60*24);
            session_destroy();
        }
    }
}