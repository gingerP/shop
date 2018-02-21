<?php
include_once('src/back/labels/HttpStatuses.php');
try {
    define('AU_ROOT', __DIR__.'/../../../');
    $config = parse_ini_file('config/config.ini');
    $messages = parse_ini_file('config/messages.ini');
    $GLOBALS['AU_MESSAGES'] = $messages;

    $GLOBALS['config'] = $config;
    define('AU_CONFIG', $config);
    $GLOBALS['AU_SEC_PROTOCOL'] = 'HTTPS';
    $GLOBALS['REDIRECT_PROTOCOL'] = 'https';

    /*header('Content-type: application/json; charset=UTF-8');*/
    include_once('src/back/import/import');
    include_once('src/back/import/admin_pages');
    include_once('src/back/import/service');
    include_once('src/back/import/errors');

    function loginRedirect($page, $extend = [])
    {
        SessionManager::sessionDestroy();
        if ($page == 'login') {
            //AUTH failed or auth not from login page
            $page = new AdminPageLogin($extend);
            echo $page->getHtml();
        } else {
            //default redirect
            $host = $_SERVER['HTTP_HOST'];
            header("Location: ".$GLOBALS['REDIRECT_PROTOCOL']."://$host/admin/login");
            exit;
        }
    }

    function hasCredentials() {
        $POST = Utils::getPostSource();
        return array_key_exists(Constants::LOGIN_USER, $POST) ||
            array_key_exists(Constants::LOGIN_PASSWORD, $POST);
    }

    $page = $_GET['page'];
    if ($page == 'redirect') {
        $host = $_SERVER['HTTP_HOST'];
        header("Location: ".$GLOBALS['REDIRECT_PROTOCOL']."://$host/admin/login");
        exit;
    }
    $sessionStarted = SessionManager::sessionStart();

    if ($sessionStarted) {
        if (AuthManager::isAuth()) {
            //AUTH SUCCESS
            switch ($page) {
                case 'settings':
                    $page = new SettingsPage();
                    echo $page->getHtml();
                    break;
                case 'booklets':
                    $page = new AdminPageBooklets();
                    echo $page->getHtml();
                    break;
                case 'contacts':
                case 'prices':
                case 'tree':
                case 'goods':
                case 'bookletpreview':
                    $page = new AdminPageProducts();
                    echo $page->getHtml();
                    break;
                case 'logout':
                    loginRedirect($page);
                    break;
                default:
                    $page = new AdminPageProducts();
                    echo $page->getHtml();
                    break;
            }
        } else if ($page == 'login' && hasCredentials()) {
            if (AuthManager::makeAuth()) {
                //check AUTH
                $host = $_SERVER['HTTP_HOST'];
                header("Location: ".$GLOBALS['REDIRECT_PROTOCOL']."://$host/admin/goods");
                exit;
            } else {
                loginRedirect($page, ['login_failed' => true]);
            }
        } else {
            loginRedirect($page);
        }
    } else {
        loginRedirect($page);
    }

} catch (Exception $e) {
    http_response_code(HttpStatuses::INTERNAL_SERVER_ERROR);
    header('Content-Type: application/json');
    echo json_encode(new InternalError($e));
} catch (Error $e) {
    http_response_code(HttpStatuses::INTERNAL_SERVER_ERROR);
    header('Content-Type: application/json');
    echo json_encode(new InternalError($e));
}