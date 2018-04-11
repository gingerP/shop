<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vinni
 * Date: 03.09.13
 * Time: 22:50
 * To change this template use File | Settings | File Templates.
 */
include_once AuWebRoot.'/src/back/import/import.php';
include_once AuWebRoot.'/src/back/import/pages.php';
 AuWebRoot.'/src/back/import/tags.php';

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
        $container->addStyleClasses(["bottom_panel_item"]);
        $container->updateId("bottom_catalog_tree");
        $containerChild = new Div();
        $containerChild->addStyleClasses(["catalog", "content"]);
        $container->addChild($containerChild);
        $div01->addChildList([$container]);
        $mainTitle = new A();
        $mainTitle->addChild("Каталог");
        $mainTitle->addAttribute("href", Labels::$TOP_NAVIGATION_LINKS["catalog"]);
        $mainTitle->addStyleClasses(["f-16", "title"]);
        $containerChild->addChild($mainTitle);

        $treeContainer = new Div();
        $treeContainer->addStyleClass('bottom_panel_item_placeholder');
        $containerChild->addChild($treeContainer);
        $this->treeProcessBottom($this->mainTree, $treeContainer);

        $vkContainer = new Div();
        $vkContainer->addStyleClasses(["bottom_panel_item"]);

        $subVk = new Div();
        $subVk->addStyleClass("content");
        $vkContainer->addChild($subVk);

        $vkTitle = new A();
        $vkTitle->addAttribute("href", "https://vk.com/club143927701");
        $vkTitle->addAttribute("target=", "_blank");
        $vkTitle->addChild("Вконтакте");
        $vkTitle->addStyleClasses(["f-16", "title"]);

        $subVk->addChildren($vkTitle);
        $this->bottomVkRender($this->mainTree, $subVk);
        $div01->addChild($vkContainer);

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

    private function bottomVkRender(Tree &$tree, Tag &$tag) {
        $mainDiv = new Div();
        $mainDiv->addStyleClasses(["f-17", "cursor_pointer", "bottom_panel_item_placeholder", "label"]);
        $mainDiv->addChild("
            <script type=\"text/javascript\" src=\"//vk.com/js/api/openapi.js?144\"></script>
                <!-- VK Widget -->
                <div id=\"vk_groups\" style='height: 320px;'></div>
                <script type=\"text/javascript\">
                VK.Widgets.Group(\"vk_groups\", {mode: 0, width: 'auto'}, 143927701);
            </script>
        ");
        $tag->addChild($mainDiv);
        return $mainDiv;
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