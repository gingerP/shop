<?php
include_once AuWebRoot.'/src/back/import/db.php';
include_once AuWebRoot.'/src/back/import/pages.php';
use Katzgrau\KLogger\Logger as Logger;

class CatalogLoader
{

    public $dataTotalCount;
    public $dataCount;
    public $data;
    private $logger;

    public function __construct()
    {
        $this->logger = new Logger(AU_CONFIG['log.file'], AU_CONFIG['log.level']);
    }

    private function isAdminOrderEnabled()
    {
        $preference = new DBPreferencesType();
        return $preference->getPreference(SettingsNames::USE_ADMIN_ORDER)[DB::TABLE_PREFERENCES__VALUE] == 'true';
    }

    public function getItemsMainData($pageNumber, $num)
    {
        $Products = new DBGoodsType();
        $limitBegin = ($pageNumber - 1) * $num;
        $limitEnd = $num;
        if ($this->isAdminOrderEnabled()) {
            $Products->getAdminSortedForCommon($limitBegin, $limitEnd);
        } else {
            $Products->executeRequestWithLimit('', '', DB::TABLE_GOODS___ORDER, DB::ASC, $limitBegin, $limitEnd);
        }
        $this->data = $Products->extractDataFromResponse($Products->getResponse());
        $this->dataCount = $Products->getResponseSize();
        $Products->executeTotalCount();
        $this->dataTotalCount = intval($Products->getTotalCount());
    }

    public function getItemsForCategory($pageNumber, $num, $key)
    {
        $Products = new DBGoodsType();
        $navKeys = new DBNavKeyType();
        $treeUtils = new TreeUtils();
        $navKeys->executeRequest('', '', DB::TABLE_ORDER);
        $tree = $treeUtils->buildTreeByLeafs();
        $keys = $treeUtils->getTreeLeafesForKey($tree, $key);
        $Products->getGoodsKeyCount($keys);
        $this->dataTotalCount = $Products->getTotalCount();
        $limitBegin = ($pageNumber - 1) * $num;
        $limitEnd = $num;
        if ($this->isAdminOrderEnabled()) {
            $Products->getUserSortedForMenu($keys, $limitBegin, $limitEnd);
        } else {
            $str = implode('|', $keys);
            $Products->executeRequestRegExpWithLimit(DB::TABLE_GOODS__CATEGORY, "^(" . $str . "){1}", DB::TABLE_GOODS___ORDER, DB::ASC, $limitBegin, $limitEnd);
        }
        $this->data = $Products->extractDataFromResponse($Products->getResponse());
        $this->dataCount = count($this->data);
    }

    public function getItemSearchData($pageNumber, $num, $valueToSearch)
    {
        $Products = new DBGoodsType();
        $searchResult = $Products->searchByNameDescription($valueToSearch, $pageNumber * $num, $num);
        $this->data = $searchResult['list'];
        $this->dataCount = count($searchResult['list']);
        $this->dataTotalCount = intval($searchResult['totalCount']);
    }

}