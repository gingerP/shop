<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vinni
 * Date: 03.09.13
 * Time: 22:50
 * To change this template use File | Settings | File Templates.
 */
include_once("import");
include_once("page");
include_once("tag");

class BottomPanel {

    private $mainTree;
    private $treeLevel;

    public function BottomPanel() {}

    public function getDom() {
        $tree = new TreeView();
        $this->mainTree = $tree->getMainTree();
        return $this->createBottomTreeDom();
    }

    private function createBottomTreeDom() {
        $mainDiv = new Div();
        $mainDiv->addStyleClass("bottom_panel");
        $div01 = new Div();
        $mainDiv->addChild($div01);
        $div01->addStyleClasses(["bottom_panel_window"]);
        $container = new Div();
        $container->addStyleClasses(["bottom_panel_item", "w-31p"]);
        $container->updateId("bottom_catalog_tree");
        $containerChild = new Div();
        $containerChild->addStyleClasses(["catalog"]);
        $container->addChild($containerChild);
        $container2 = new Div();
        $container2->addStyleClasses(["bottom_panel_item", "w-31p"]);
        $div01->addChildList([$container, $container2]);
        $mainTitle = new A();
        $mainTitle->addChild("Каталог");
        $mainTitle->addAttribute("href", Labels::$TOP_NAVIGATION_LINKS["catalog"]);
        $mainTitle->addStyleClasses(["f-16", "title"]);
        $containerChild->addChild($mainTitle);

        $treeContainer = new Div();
        $treeContainer->addStyleClass("content");
        $containerChild->addChild($treeContainer);
        $this->treeProcessBottom($this->mainTree, $treeContainer);

        return $mainDiv;
    }

    private function treeProcessBottom(Tree &$tree, Tag &$tag) {
        $ul = new Ul();
        $isRoot = $tree->key == 'GN';
        $isRootChild = $tree->parentKey == 'GN';
        if (!$isRoot) {
            $currentLi = $this->bottomTreeRender($tree, $isRootChild? "is_root": "");
            if ($isRootChild) {
            }
            $tag->addChild($currentLi);
        }
        if (count($tree->childrens) != 0) {
            if (!$isRoot) {
                $currentLi->addChild($ul);
            } else {
                $tag->addChild($ul);
            }
            $ul->addStyleClasses(array("container", "text_bottom_tree"));
            $this->treeLevel++;
            foreach($tree->childrens as $treeChild) {
                $this->treeProcessBottom($treeChild, $ul);
            }
            $this->treeLevel--;
        }
    }

    private function bottomTreeRender(Tree &$tree, $isRoot) {
        $children = count($tree->childrens);
        $span = new Li();
        $mainDiv = new Div();
        $span->addStyleClass($isRoot);
        /*if ($children == 0) {*/
            $mainDiv = new A();
            $span->addChild($mainDiv);
            $mainDiv->addStyleClasses(["f-17", "cursor_pointer", "bottom_tree_hover", "label"]);
            $mainDiv->addAttribute(TagLabels::HREF, URLBuilder::getCatalogLinkForTree($tree->key));
        /*} else {
            $span->addChild($mainDiv);
            $mainDiv->addStyleClasses(["f-17", "label"]);
        }*/
        $mainDiv->addChild($tree->value);
        return $span;
    }

    private function remainingLinks() {
        $mainDiv = new Div();
        for($index = 1; $index < count(Labels::$BOTTOM_NAVIGATION_KEYS); $index++) {
            $div01 = new Div();
            $mainDiv->addChild($div01);
            $div01->addStyleClasses(["input_hover", "panel_btn", "float_left", "bottom_tree_main_title", "cursor_pointer", "bottom_remaining_link"]);
            $div01->addAttribute(TagLabels::ON_CLICK, Utils::getWindowOnclickValue(Labels::$TOP_NAVIGATION_LINKS[Labels::$BOTTOM_NAVIGATION_KEYS[$index]]));
            $div01->addChild(Labels::$TOP_NAVIGATION_TITLE[Labels::$BOTTOM_NAVIGATION_KEYS[$index]]);
        }
        return $mainDiv;
    }

}