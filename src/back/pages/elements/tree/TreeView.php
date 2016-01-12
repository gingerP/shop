<?php
include_once("import");
include_once("page");
include_once("db");
include_once("tag");

class TreeView {
    private $DEFAULT_TREE_LEVEL_TO_SHOW = 2;
    private $mainTree;
    private $treeLevel;

    public function TreeView() {
        $treeUtils = new TreeUtils();
        $this->mainTree = $treeUtils->buildTreeByLeafs();
    }

    public function getAllLabels() {
        $navKeyType = new DBNavKeyType();
        $navKeys = $navKeyType->getList();
        $result = [];
        while ($row = mysql_fetch_array($navKeys)) {
            array_push($result, $row[DB::TABLE_NAV_KEY__VALUE]);
        }
        return $result;
    }

    public function createTree(&$openBranch) {
        $treeUtils = new TreeUtils();
        $treeUtils->openBranch($this->mainTree, $openBranch);
        $mainDiv = new Ul();
        $mainDiv->addStyleClasses(["tree", "f-16"]);
        $this->treeLevel=0;
        $tree = $this->createRootNode();
        $tree->childrens = array_merge($tree->childrens, $this->mainTree->childrens);
        $this->treeProcess($tree, $mainDiv, $openBranch);

        $arr = $treeUtils->getTreeLeafes($this->mainTree);
        return $mainDiv;
    }

    private function treeProcess(Tree &$tree, Tag &$tag, $openBranch) {
        $mainLi = new Li();
        if ($tree->key != Labels::BLANK) {
            $mainLi = $this->mainTreeRender($tree, $openBranch);
            $tag->addChild($mainLi);
            if (count($tree->childrens) != 0) {
                $ul = new Ul();
                $mainLi->addChild($ul);
                $ul->addStyleClass("Container");
                $this->treeLevel++;
                $ul->addStyleClass($this->treeLevel <= $this->DEFAULT_TREE_LEVEL_TO_SHOW || $tree->show? "tree_node_open": "tree_node_close");
                foreach($tree->childrens as $treeChild) {
                    $this->treeProcess($treeChild, $ul, $openBranch);
                }
                $this->treeLevel--;
            }
        }
        $mainLi->addStyleClass($tree->key == $openBranch? 'selected_node': '');
    }

    private function mainTreeRender(Tree &$tree, $selectedKey) {
        $li = new Li();
        $mainDiv = new Div();
        $mainDiv->addStyleClasses(["expand", "text_non_select", "tree_text_node", "input_hover"]);
        $table = new Table();
        $tr = new Tr();
        $nodeIcon = new Td();
        $nodeText = new Td();
        $nodeText->addStyleClass("tree_text");
        $nodeSearchCount = new Td();
        $nodeSearchCount->addStyleClass("tree_search_count");
        if (count($tree->childrens) > 0 ) {
            $nodeIcon->addStyleClasses(["tree_btn"]);
            $icon = new Img();
            $icon->addAttribute("style","top: 2px; position: relative; margin: 0 5px;");
            $icon->addAttribute("src", $this->treeLevel <= $this->DEFAULT_TREE_LEVEL_TO_SHOW || $tree->show? "images/arrow90.png": "images/arrow00.png");
            $nodeIcon->addChild($icon);
        } else {
            $nodeIcon->addStyleClass("tree_empty");
        }
        $link = new A();
        $link->addAttribute("href", URLBuilder::getCatalogLinkForTree($tree->key));
        $link->addChild($tree->value);
        $link->addStyleClass("input_hover");
        $nodeSelected = new Div();
        $nodeSelected->addStyleClass($tree->key == $selectedKey? 'selected': 'tree_empty');
        $link->addChild($nodeSelected);
        $nodeText->addChild($link);
        return $li->addChild($mainDiv->addChild($table->addChild($tr->addChildList([$nodeIcon, $nodeText, $nodeSearchCount]))));
    }

    public function getMainTree() {
        return $this->mainTree;
    }

    private function createRootNode() {
        $rootNode = new Tree('', 'GN', 'Каталог', true, true);
        return $rootNode;
    }
}