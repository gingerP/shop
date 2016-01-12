<?php
header('Content-type: application/json; charset=UTF-8');
include_once('service');
if (array_key_exists('method', $_GET)) {
    $method = $_GET['method'];
    function checkAccess($methodName) {
        $result = true;
        $securedMethods = [
            "getGoods",
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
            "getBookletBackgrounds"
        ];
        if (in_array($methodName, $securedMethods)) {
            $result = isset($_SERVER['HTTPS']) && SessionManager::sessionStart() && AuthManager::isAuth();
        }
        return $result;
    }
    if (checkAccess($method)) {
        try {
            switch ($method) {
                case 'getPrices':
                    echo json_encode(PriceService::getPrices());
                    break;
                case 'getAddresses':
                    echo json_encode(AddressService::getAddresses());
                    break;
                case 'getGoods':
                    echo json_encode(GoodsService::getGoods(-1));
                    break;
                case 'updateGood':
                    $id = Utils::getFromPOST('id');
                    $data = Utils::getFromPOST('data');
                    echo json_encode(GoodsService::updateGood($id, $data));
                    break;
                case 'getGood':
                    $id = Utils::getFromPOST('id');
                    echo json_encode(GoodsService::getGood($id));
                    break;
                case 'getGoodImages':
                    $id = Utils::getFromPOST('id');
                    echo json_encode(GoodsService::getImages($id));
                    break;
                case 'deleteGood':
                    $id = Utils::getFromPOST('id');
                    echo json_encode(GoodsService::deleteGood($id));
                    break;
                case 'sendFeedbackEmail':
                    $messageBody = Utils::getFromPOST('message_body');
                    $senderEmail = Utils::getFromPOST('sender_email');
                    $senderName = Utils::getFromPOST('sender_name');
                    echo json_encode(EmailService::sendFeedbackEmail($messageBody, $senderEmail, $senderName));
                    break;
                case 'getDescriptionKeys':
                    echo json_encode(DescriptionKeys::$keys);
                    break;
                case 'getGoodsKeys':
                    echo json_encode(GoodsKeysService::getList());
                    break;
                case 'getNextGoodCode':
                    $code = Utils::getFromPOST('code');
                    echo json_encode(GoodsService::getNextGoodCode($code));
                    break;
                case 'uploadImagesForGood':
                    $id = Utils::getFromPOST('id');
                    $data = $_POST['data'];
                    $oldKey = Utils::getFromPOST('old_good_key');
                    echo json_encode(GoodsService::updateImages($id, $oldKey, $data));
                    break;
                case 'updatePrices':
                    $data = $_POST['data'];
                    echo json_encode(PriceService::updatePrices($data));
                    break;
                /*****************************************Booklets*************************************/
                case 'listBooklets':
                    $mapping = Utils::getFromPOST('mapping');
                    echo json_encode(BookletService::getList($mapping));
                    break;
                case 'getBooklet':
                    //json_encode() will execute in BookletService::get() method
                    $id = Utils::getFromPOST('id');
                    $mapping = Utils::getFromPOST('mapping');
                    echo BookletService::get($id, $mapping);
                    break;
                case 'saveBooklet':
                    $data = $_POST['data'];
                    echo json_encode(BookletService::save($data));
                    break;
                case 'deleteBooklet':
                    $id = Utils::getFromPOST('id');
                    echo json_encode(BookletService::delete($id));
                    break;
                case 'getBookletBackgrounds':
                    echo json_encode(BookletService::getBookletBackgroundImages());
                    break;
                default:
                    throw new Exception('Method not found!');
            }
        }catch (Exception $e) {
            echo json_encode($e->getMessage());
        }
    } else {
        echo json_encode("Access denied!");
    }
}