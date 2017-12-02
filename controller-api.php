<?php
try {
    $config = parse_ini_file('config/config.ini');
    $GLOBALS['config'] = $config;

    header('Content-type: application/json; charset=UTF-8');
    http_response_code(200);

    include_once('src/back/import/service');
    if (array_key_exists('method', $_GET)) {
        $method = $_GET['method'];
        function checkAccess($methodName)
        {
            $result = true;
            $securedMethods = [
                "getGoods",
                "getAdminOrder",
                "updateGood",
                "getGood",
                "getGoodImages",
                "deleteGood",
                "getDescriptionKeys",
                "getGoodsKeys",
                "getNextGoodCode",
                "uploadImagesForGood",
                "updatePrices",
                "listBooklets",
                "getBooklet",
                "saveBooklet",
                "deleteBooklet",
                "getBookletBackgrounds",
                "saveOrder"
            ];
            if (in_array($methodName, $securedMethods)) {
                $result = isset($_SERVER['HTTPS']) && SessionManager::sessionStart() && AuthManager::isAuth();
            }
            return $result;
        }

        if (checkAccess($method)) {
            $responseData = [];
            switch ($method) {
                case 'getPrices':
                    $responseData = PriceService::getPrices();
                    break;
                case 'getAddresses':
                    $responseData = AddressService::getAddresses();
                    break;
                case 'getGoods':
                    $responseData = GoodsService::getGoods(-1);
                    break;
                case 'updateGood':
                    $id = Utils::getFromPOST('id');
                    $data = Utils::getFromPOST('data');
                    $responseData = GoodsService::updateGood($id, $data);
                    break;
                case 'getGood':
                    $id = Utils::getFromPOST('id');
                    $responseData = GoodsService::getGood($id);
                    break;
                case 'getGoodImages':
                    $id = Utils::getFromPOST('id');
                    $responseData = GoodsService::getImages($id);
                    break;
                case 'deleteGood':
                    $id = Utils::getFromPOST('id');
                    $responseData = GoodsService::deleteGood($id);
                    break;
                case 'saveOrder':
                    $data = Utils::getFromPOST('order');
                    $responseData = GoodsService::saveGoodsOrder($data);
                    break;
                case 'getAdminOrder':
                    $responseData = GoodsService::getGoodsOrder();
                    break;
                case 'sendFeedbackEmail':
                    $messageBody = Utils::getFromPOST('message_body');
                    $senderEmail = Utils::getFromPOST('sender_email');
                    $senderName = Utils::getFromPOST('sender_name');
                    $responseData = EmailService::sendFeedbackEmail($messageBody, $senderEmail, $senderName);
                    break;
                case 'getDescriptionKeys':
                    $responseData = DescriptionKeys::$keys;
                    break;
                case 'getGoodsKeys':
                    $responseData = GoodsKeysService::getList();
                    break;
                case 'getNextGoodCode':
                    $code = Utils::getFromPOST('code');
                    $responseData = GoodsService::getNextGoodCode($code);
                    break;
                case 'uploadImagesForGood':
                    $id = Utils::getFromPOST('id');
                    $data = $_POST['data'];
                    $oldKey = Utils::getFromPOST('old_good_key');
                    $responseData = GoodsService::updateImages($id, $oldKey, $data);
                    break;
                case 'updatePrices':
                    $data = $_POST['data'];
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
                /*****************************************Booklets*************************************/
                case 'listBooklets':
                    $mapping = Utils::getFromPOST('mapping');
                    $responseData = BookletService::getList($mapping);
                    break;
                case 'getBooklet':
                    //json_encode() will execute in BookletService::get() method
                    $id = Utils::getFromPOST('id');
                    $mapping = Utils::getFromPOST('mapping');
                    echo BookletService::get($id, $mapping);
                    break;
                case 'saveBooklet':
                    $data = $_POST['data'];
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
                default:
                    http_response_code(401);
                    $responseData = new BaseError('Method not found!', 500);
            }
            if (!$responseData) {
                $responseData = [];
            }
            echo json_encode($responseData);
        } else {
            http_response_code(401);
            echo '{}';
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode($e);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode($e);
}