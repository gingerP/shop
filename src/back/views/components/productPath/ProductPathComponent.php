<?php

include_once AuWebRoot.'/src/back/views/components/AbstractComponent.php';

class ProductPathComponent extends AbstractComponent
{

    private $categoryCode;

    public function __construct($categoryCode)
    {
        parent::__construct();
        $this->categoryCode = $categoryCode;
    }

    public function build() {
        $treeUtils = new TreeUtils();
        $mainTree = $treeUtils->buildTreeByLeafs();
        $path = $treeUtils->getTreePath($mainTree, $this->categoryCode);
        $preparedPath = [];
        foreach ($path as &$item) {
            if ($item->key !== 'GN') {
                $item->nextItemArrow = true;
                $item->link = URLBuilder::getCatalogLinkForTree($item->key);
                array_push($preparedPath, $item);
            }
        }
        $count = count($preparedPath);
        if ($count > 0) {
            $preparedPath[$count - 1]->nextItemArrow = false;
        }
        $tpl = parent::getEngine()->loadTemplate('components/productPath/product-path.mustache');
        return $tpl->render(['path' => $preparedPath]);
    }
}