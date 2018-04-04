<?php
include_once('src/back/labels/HttpStatuses.php');

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Content-type: application/json; charset=UTF-8');
http_response_code(HttpStatuses::OK);

define('AU_ROOT', __DIR__.'/../../../');
error_reporting(-1);
include_once('src/back/import/db');
include_once('src/back/import/service');
include_once('src/back/import/errors');

function catchInternalError($error) {
    header('Content-Type: application/json');
    $errorModel = new DBErrorType();
    $errorModel->createException($error);
    error_log($error->getMessage());
    if ($error instanceof BaseError) {
        http_response_code($error->status);
        echo json_encode($error->toJson());
        return;
    }
    http_response_code(HttpStatuses::INTERNAL_SERVER_ERROR);
    $internalError = new InternalError($error);
    echo json_encode($internalError->toJson());
}
try {
    $config = parse_ini_file('config/config.ini');
    $messages = parse_ini_file('config/messages.ini');
    $GLOBALS['config'] = $config;
    define('AU_CONFIG', $config);
    $GLOBALS['AU_MESSAGES'] = $messages;
    $GLOBALS['AU_SEC_PROTOCOL'] = 'HTTPS';

    if (array_key_exists('method', $_GET)) {
        $method = $_GET['method'];
        function checkAccess($methodName)
        {
            $result = true;
            $securedMethods = [
                'getGoods',
                'getAdminOrder',
                'updateGood',
                'getGood',
                'getGoodImages',
                'deleteGood',
                'getDescriptionKeys',
                'getGoodsKeys',
                'getAdminSettings',
                'getNextGoodCode',
                'uploadImagesForGood',
                'updatePrices',
                'listBooklets',
                'getBooklet',
                'saveBooklet',
                'deleteBooklet',
                'getBookletBackgrounds',
                'saveOrder',
                'saveError'
            ];
            if (in_array($methodName, $securedMethods)) {
                $result = isset($_SERVER[$GLOBALS['AU_SEC_PROTOCOL']]) && SessionManager::sessionStart() && AuthManager::isAuth();
            }
            return $result;
        }

        if (checkAccess($method)) {
            $responseData = [];
            switch ($method) {
                case 'getAdminSettings':
                    $responseData = PreferencesService::getAdminPreferences();
                    break;
                case 'getPrices':
                    $responseData = PriceService::getPrices();
                    break;
                case 'getAddresses':
                    $responseData = AddressService::getAddresses();
                    break;
                case 'getGoods':
                    $responseData = ProductsService::getGoods(-1);
                    break;
                case 'updateGood':
                    ProductsService::validate_updateGood();
                    $id = Utils::getFromPOST('id');
                    $data = Utils::getFromPOST('data');
                    $responseData = ProductsService::updateGood($id, $data);
                    break;
                case 'getGood':
                    $id = Utils::getFromPOST('id');
                    $responseData = ProductsService::getGood($id);
                    break;
                case 'deleteGood':
                    $id = Utils::getFromPOST('id');
                    Log::db($id);
                    $responseData = ProductsService::deleteGood($id);
                    break;
                case 'saveOrder':
                    $data = Utils::getFromPOST('order');
                    $responseData = ProductsService::saveGoodsOrder($data);
                    break;
                case 'getAdminOrder':
                    $responseData = ProductsService::getGoodsOrder();
                    break;
                case 'sendFeedbackEmail':
                    $messageBody = Utils::getFromPOST('message_body');
                    $senderEmail = Utils::getFromPOST('sender_email');
                    $senderName = Utils::getFromPOST('sender_name');
                    $product = Utils::getFromPOST('product', false);
                    $responseData = EmailService::sendFeedbackEmail($messageBody, $senderEmail, $senderName, $product);
                    break;
                case 'getDescriptionKeys':
                    $responseData = DescriptionKeys::$keys;
                    break;
                case 'getGoodsKeys':
                    $responseData = GoodsKeysService::getList();
                    break;
                case 'getNextGoodCode':
                    $code = Utils::getFromPOST('code');
                    $responseData = ProductsService::getNextGoodCode($code);
                    break;
                case 'uploadImagesForGood':
                    ProductsService::validate_updateImages();
                    $id = Utils::getFromPOST('id');
                    $data = Utils::getFromPOST('data', false);
                    $responseData = ProductsService::updateImages($id, $data);
                    break;
                case 'updatePrices':
                    $data = Utils::getFromPOST('data');
                    $responseData = PriceService::updatePrices($data);
                    break;
                case 'loadNews':
                    $page = Utils::getFromPOST('page');
                    $offset = Utils::getFromPOST('offset');
                    $responseData = NewsService::loadNews($page, $offset);
                    break;
                case 'search':
                    $search = Utils::getFromGET('search');
                    $page = Utils::getFromGETWithDefault('page', 0);
                    $offset = Utils::getFromGETWithDefault('limit', 10);
                    $responseData = SearchService::search($search, $page, $offset);
                    break;
                case 'saveError':
                    $message = Utils::getFromPOST('message', false);
                    $stack = Utils::getFromPOST('stack', false);
                    $pageUrl = Utils::getFromPOST('pageUrl', false);
                    $responseData = ErrorsService::saveError($message, $stack, $pageUrl);
                    break;
                /*****************************************Booklets*************************************/
                case 'listBooklets':
                    $mapping = Utils::getFromPOST('mapping');
                    $responseData = BookletService::getList($mapping);
                    break;
                case 'getBooklet':
                    //json_encode() will execute in BookletService::get() method
                    $id = Utils::getFromPOST('id');
                    $mapping = Utils::getFromPOST('mapping');
                    $responseData = BookletService::get($id, $mapping);
                    break;
                case 'saveBooklet':
                    $data = Utils::getFromPOST('data', false);
                    $responseData = BookletService::save($data);
                    break;
                case 'deleteBooklet':
                    $id = Utils::getFromPOST('id');
                    $responseData = BookletService::delete($id);
                    break;
                case 'getBookletBackgrounds':
                    $responseData = BookletService::getBookletBackgroundImages();
                    break;
                case 'getPublicPreferences':
                    $responseData = PreferencesService::getPublicPreferences();
                    break;
                case 'readImagesFromCatalogToDb':
                    $responseData = ProductsService::readImagesFromCatalogToDb();
                    break;
                default:
                    http_response_code(HttpStatuses::NOT_FOUND);
                    $responseData = (new NotFoundError("Method '$method' not found."))->toJson();
            }
            if (!$responseData) {
                $responseData = [];
            }
            echo json_encode($responseData);
        } else {
            throw new UnAuthorizedError( $GLOBALS['AU_MESSAGES']['session_expired']);
        }
    }
} catch (Exception $e) {
    catchInternalError($e);
} catch (Error $e) {
    catchInternalError($e);
}