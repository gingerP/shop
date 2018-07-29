<?php
include_once AuWebRoot.'/src/back/import/import.php';
class Labels {
    const SEARCH = "search";
    const DOWNLOAD = "download";
    const MAP = "map";
    const CONTACTS = "contacts";
    const BLANK = "blank";
    public static $TOP_NAVIGATION_KEYS = array('main', 'catalog', 'contacts', 'delivery', 'search');
    public static $BOTTOM_NAVIGATION_KEYS = array('catalog', 'contacts');
    public static $TOP_NAVIGATION_LINKS = array(
        "main" => "/",
        "catalog" => "/catalog",
        "search" => "/catalog",
        "delivery" => "/delivery",
        "download" => "",
        "contacts" => "/contacts",
    );
    public static $TOP_NAVIGATION_TITLE = array(
        "main" => "Главная",
        "catalog" => "Каталог",
        "delivery" => "Доставка почтой",
        "search" => "Поиск",
        "download" => "Прайс-лист",
        "contacts" => "Контакты"
    );
    public static $TOP_NAVIGATION_ICONS = array(
        "main" => "/images/icons/home.svg"
    );
    public static $VIEW_MODE_NUMERIC = array(10, 30, 100);
    const VIEW_MODE_NUMERIC_DEF = 50;
    /*public static $VIEW_MODE_COMPACT = array("list" => "список", "compact" => "компактный", "extend" => "расширенный");*/
    public static $VIEW_MODE_COMPACT = array("compact" => "компактный", "extend" => "расширенный", "metro" => "metro");
    const VIEW_MODE_COMPACT_DEF = "metro";

    const STORE_MODE_UR = "для юридических лиц";
    const STORE_MODE_FIZ = "для физических лиц";
    const CHECK_UR = "check_ur";
    const CHECK_FIZ = "check_fiz";
    const MAIN_PARAMS = "MAIN_PARAMS";
    const ADDITIONAL_PARAMS = "ADDITIONAL_PARAMS";

    const MAIN_ECHO = "MAIN_ECHO";
    const EMPTY_SEARCH_RESULT = "По вашему запросу<br> \"{0}\"<br> ничего не найдено.";

    const CAP_IMAGES_ROOT = "/images/catalog/caps/";
    const CAP_IMAGE_FOR_CLOTHING = "clothing.jpg";
    const CAP_IMAGE_FOR_TOOLS = "tools.png";
    const AUTH_SESSION_ID = "session_id";

    public static function prefillMessage($values, $message) {
        for($valueIndex = 0; $valueIndex < count($values); $valueIndex++) {
            $pos = strpos($message, "{$valueIndex}");
            if ($pos !== false) {
                $message = str_replace('{'.$valueIndex.'}', $values[$valueIndex], $message);
            }
        }
        return $message;
    }
}