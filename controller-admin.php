<?php
/*header('Content-type: application/json; charset=UTF-8');*/
include_once('import');
include_once('admin_pages');
include_once('service');
$page = $_GET['page'];
if ($page == 'redirect') {
    $host  = $_SERVER['HTTP_HOST'];
    header("Location: https://$host/admin/login");
    exit;
}
$sessionStarted = SessionManager::sessionStart();

if ($sessionStarted) {
    if (AuthManager::isAuth()) {
    //AUTH SUCCESS
        switch ($page) {
            case 'contacts':
                $page = new AdminPage_Contacts();
                echo $page->getHtml();
                break;
            case 'prices':
                $page = new AdminPage_Prices();
                echo $page->getHtml();
                break;
            case 'tree':
                $page = new AdminPage_Tree();
                echo $page->getHtml();
                break;
            case 'goods':
                $page = new AdminPage_Goods();
                echo $page->getHtml();
                break;
            case 'booklets':
                $page = new AdminPage_Booklet();
                echo $page->getHtml();
                break;
            case 'bookletpreview':
                $id = Utils::getFromGET('id');
                $page = new AdminPage_Booklet($id);
                echo $page->getHtml();
                break;
            case 'logout':
                loginRedirect($page);
            default:
                $page = new AdminPage_Goods();
                echo $page->getHtml();
                break;
        }
    } else if ($page == 'login' && AuthManager::makeAuth()) {
    //check AUTH
        $host  = $_SERVER['HTTP_HOST'];
        header("Location: https://$host/admin/goods");
        exit;
    } else {
        loginRedirect($page);
    }
} else {
    loginRedirect($page);
}

function loginRedirect($page) {
    SessionManager::sessionDestroy();
    if ($page == 'login') {
        //AUTH failed or auth not from login page
        $page = new AdminPage_Login();
        echo $page->getHtml();
    } else {
        //default redirect
        $host  = $_SERVER['HTTP_HOST'];
        header("Location: https://$host/admin/login");
        exit;
    }
}
