<?php
include_once("import");
include_once("db");
include_once("page");

class TreeUtils {
    private $DEFAULT_TREE_LEVEL_TO_SHOW = 2;
    private $leaves = array();
    private $leafes = array();
    private $pathToLeaf = array();
    private $key = '';
    private $results = array();
    private $count = 0;
    private $responseSize = 0;

    public function buildTree($arr, $parentKey) {
        $parent = new Tree('', $parentKey, '', true, '');
        $nodes = [];
        while ($row = mysql_fetch_array($arr)) {
            array_push($this->results, $row);
            array_push($nodes, new Tree($row[DB::TABLE_NAV_KEY__PARENT_KEY], $row[DB::TABLE_NAV_KEY__KEY_ITEM],
                $row[DB::TABLE_NAV_KEY__VALUE], true, $row[DB::TABLE_NAV_KEY__HOME_VIEW]));
        }
        for($itemIndex = 0; $itemIndex < count($nodes); $itemIndex++) {
            if ($nodes[$itemIndex]->parentKey == 'GN') {
                array_push($parent->childrens, $nodes[$itemIndex]);
            }
            for ($childIndex = 0; $childIndex < count($nodes); $childIndex++) {
                if ($nodes[$childIndex]->parentKey == $nodes[$itemIndex]->key) {
                    array_push($nodes[$itemIndex]->childrens, $nodes[$childIndex]);
                }
            }
        }
        return $parent;
    }

    public function openBranch(Tree &$tree, $key) {
        $this->key = $key;
        $this->fillingTree($tree, 'ExpandClose');
        $this->openBranchR($tree, $key);
    }

    private function openBranchR(Tree &$tree, $key) {
        if ($tree->key == $key) {
            $tree->show = true;
            return true;
        } else if (count($tree->childrens) != 0){
            foreach($tree->childrens as $children) {
                $isNodeAchieved = $this->openBranchR($children, $key);
                if ($isNodeAchieved) {
                    $tree->show = true;
                    return $isNodeAchieved;
                } else {
                    $tree->show = false;
                }
            }
        }
        return false;
    }

    private function fillingTree(Tree &$tree, $show) {
/*        if (count($tree->childrens) == 0) {
            $tree->show = 'ExpandLeaf';
        } else {
            $tree->show = $show;
            foreach($tree->childrens as $children) {
                self::fillingTree($children, $show);
            }
        }*/
    }

    public function searchAndAdd(Tree &$tree, $parentKey, $keyToAdd, $valueToAdd, $homeView) {
        if ($this->count < $this->responseSize) {
            if ($tree->key == $parentKey) {
                $this->count++;
                array_push($tree->childrens, new Tree($parentKey, $keyToAdd, $valueToAdd, '', $homeView));
                return true;
            } else {
                foreach($tree->childrens as $treeChild){
                    if ($this->searchAndAdd($treeChild, $parentKey, $keyToAdd, $valueToAdd, $homeView) === true) {
                        return true;
                    };
                }
            }
        }
        return false;
    }

    public function getTreeLeafes(Tree &$tree) {
        $this->leaves = array();
        $this->findLeavesR($tree);
        return $this->leaves;
    }

    public function getTreeLeafesForKey(Tree &$tree, $key) {
        $this->leafes = [];
        $this->findLeafesForKeyR($tree, $key);
        return $this->leafes;

    }

    private function findLeavesR(Tree &$tree) {
        if (count($tree->childrens) != 0) {
            foreach($tree->childrens as $treeChild) {
                $this->findLeavesR($treeChild);
            }
        } else {
            array_push($this->leaves, $tree->key);
            /*Log::temp("findLeavesR: ".$tree->key);*/
        }
    }

    private function findLeafesForKeyR(Tree &$tree, $key) {
        if ($tree->key == $key) {
            $this->key = 'true';
        }
        if ($this->key != 'false') {
            if (count($tree->childrens) == 0) {
                if ($this->key == 'true') {
                    array_push($this->leafes, $tree->key);
                }
            } else {
                foreach($tree->childrens as $children) {
                    $this->findLeafesForKeyR($children, $key);
                }
            }
        }
        if ($tree->key == $key) {
            $this->key = 'false';
        }
    }

    public function getTreeChildrens(Tree &$tree) {
        $childrens = array();
        foreach($tree->childrens as $treeChild) {
            array_push($childrens, $tree->key);
        }
        return $childrens;
    }

    public function getTreePath(Tree &$tree, $key) {
        $this->leafes = [];
        $this->key = $key;
        $this->treePathR($tree);
        return array_reverse($this->leafes);
    }

    private function treePathR(Tree &$tree) {
        if ($tree->key != $this->key) {
            foreach($tree->childrens as $children) {
                $this->treePathR($children);
                if ($this->key == 'true') {
                    array_push($this->leafes, $tree);
                    break;
                }
            }
        } else {
            array_push($this->leafes, $tree);
            $this->key = 'true';
        }
    }

    public function buildTreeByLeafs() {
        $navKeys = new DBNavKeyType();
        $leafsMysql = $navKeys->getLeafs();
        $leafs = array();
        while ($row = mysql_fetch_array($leafsMysql)) {
            array_push($leafs,$row[DB::TABLE_NAV_KEY__KEY_ITEM]);
        }
        $navKeys->executeRequest('', '', DB::TABLE_NAV_KEY__ID, DB::ASC);
        //build full tree
        $mainTree = $this->buildTree($navKeys->getResponse(), "GN");
        $this->clearTree($mainTree, $leafs);
        return $mainTree;
    }

    private function clearTree(Tree &$tree, &$leafs) {
        $isStay = true;
        if (count($tree->childrens) == 0) {
            if (!in_array($tree->key, $leafs)) {
                $tree->key = Labels::BLANK;
                $isStay = false;
            }
        } else {
            $isStay = false;
            for($index = 0; $index < count($tree->childrens); $index++) {
                if ($this->clearTree($tree->childrens[$index], $leafs)) {
                    $isStay = true;
                } else {
                    $tree->childrens[$index]->key = Labels::BLANK;
                }
            }
        }
        return $isStay;
    }

    public function printTree(&$tree, $index) {
        $step = "";
        if ($index == 0) {
            Log::temp("BEGIN##############################################");
        }
        for($i = 0; $i < $index; $i++) {
            $step.=".";
        }
        Log::temp($step.$tree->key);
        if (count($tree->childrens) > 0) {
            foreach($tree->childrens as $children) {
                $index++;
                $index = $this->printTree($children, $index);
            }
        }
        $index--;
        return $index;
    }
}
