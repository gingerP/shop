<?php

include_once AuWebRoot.'/src/back/import/import.php';
include_once AuWebRoot.'/src/back/import/pages.php';
include_once AuWebRoot.'/src/back/import/tags.php';
include_once AuWebRoot.'/src/back/views/components/topPanel/TopPanelComponent.php';
include_once AuWebRoot . '/src/back/views/components/categories/CategoriesComponent.php';

use Katzgrau\KLogger\Logger as Logger;

abstract class APagesCreator
{
    private $pagePrefix = "<!doctype html> <!-- HTML5 -->";
    private $isTreeVisible = false;
    private $treeKey = "";
    private $isPathLinkVisible = true;
    private $isStatusBarVisible = false;
    private $isViewModeBlockVisible = false;
    private $isTopNavigationLinksFillingWidth = true;
    private $isBottomNavigationLinksFillingWidth = true;
    private $pathLinkForTree;
    private $pathLinkForMainBlock;
    private $viewModeBlock;
    private $pageCode;
    private $titleTag;
    private $title;
    private $metaTags = [];
    private $permanentTitle = 'ЧТУП "Августово-Компани"';
    public $content = '';
    protected $isCssUglify = true;
    protected $isJsUglify = true;

    protected function __construct($pageName)
    {
        $this->pageName = $pageName;
        $this->isCssUglify = AU_CONFIG['ui.css.uglify'];
        $this->isJsUglify = AU_CONFIG['ui.js.uglify'];
        $this->logger = new Logger(AU_CONFIG['log.file'], AU_CONFIG['log.level']);
    }

    public function validate()
    {
        return $this;
    }

    public function build()
    {
        return $this;
    }

    public function getHtml()
    {
        $html = new Html('ru');
        $head = $this->createHead();
        $head->addChild(
            '
                <meta name="theme-color" content="#17A086"/>
                <meta name="google-site-verification" content="bOWvr4uXCth1WKxvHScBwFR_bb3Q_4WeWSXeYyARLGk">
                <meta name="yandex-verification" content="63479cc9e5a115aa">'
        );
        $body = $this->createBody();
        $main = new Div();
        $main->addStyleClass("main_div");
        $topNavigationLinks = $this->createTopNavigationLinks();
        $mainContainer = $this->createMainContainer();
        $bottomNavigationLinks = $this->createBottomNavigationLinks();

        $head->prependChildren($this->metaTags);
        $html->addChildList([
            $head,
            $body->addChildList([
                $topNavigationLinks,
                $main->addChildList([$mainContainer]),
                $this->getPreBottom(),
                $this->isBottomNavigationLinksFillingWidth ? $bottomNavigationLinks : null,
                $this->getSourceScripts()
            ])
        ]);
        return $this->pagePrefix . ($html->getHtml());
    }

    public function createHead()
    {
        $head = new Head();
        $head->addChild('
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
            <meta name="robots" content="index, follow">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">' . "\n");
        $head->addChild($this->getTitleTag());
        $head->addChild('
            <link rel="stylesheet" type="text/css" href="/dist/style.css" title="main"/>
            <link rel="apple-touch-icon" href="images/system/favicon-200.png">
            <meta name="apple-mobile-web-app-capable" content="yes">
            <meta name="apple-mobile-web-app-status-bar-style" content="black">
            <link rel="shortcut icon" href="images/system/favicon.png" type="image/x-icon"/>');
        $head->addChild(SearchEngines::getGoogleAnalyticScript());
        $head->addChild(SearchEngines::getYandexMetricScript());
        return $head;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function createBody()
    {
        $body = new Body();
        $body->addStyleClass($this->getPageCode());
        return $body;
    }

    public function createMainContainer()
    {
        $mainDiv = new Div();
        $mainDiv->addStyleClass("main_container");
        //TagUtils::createShadow($mainDiv);
        $div01 = new Div();
        $div01->addStyleClass("center");

        $pathLinksContainer = new Div();
        $pathLinksContainer->updateId("a_panel");

        $treeContainer = new Div();
        $treeContainer->updateId("b_panel");

        $generalContentContainer = new Div();
        $generalContentContainer->updateId("c_panel");

        $pathLink = $this->createPathLinks();
        $tree = $this->createTree();
        $generalContent = $this->createGeneralContent();
        return $mainDiv->addChild($div01->addChildList([
            $treeContainer->addChild($tree),
            $this->isPathLinkVisible ? $pathLinksContainer->addChild($pathLink) : null,
            $generalContentContainer->addChild($generalContent)]));
    }

    public function createTopNavigationLinks()
    {
        $topNavigationLinks = new TopNavigationLinks();
        $mainDiv = new Div();
        $mainDiv->addStyleClass("top_panel");
        $linkToHome = new A();
        $linkToHome->addAttribute("href", "/");
        $linkToHome->addStyleClass("logo");
        $backGround = new Div();
        $backGround->addStyleClass("top_panel_background");
        $div11 = new Div();
        $div11->addStyleClasses(array("top_bar", "border-round-7px"));
        $div111 = new Div();
        $div111->updateId("top_panel_fixed");
        $div111->addChildList([$backGround, $linkToHome]);
        //TagUtils::createShadow($div111);
        $div1111 = new Div();
        $div1111->addStyleClass("top_bar_relative");
        $mainDiv->addChildList([$div11->addChild($div111->addChild($div1111))]);
        $div1111->addChild($topNavigationLinks->getDOM($this->pageName));

        return (new TopPanelComponent($this->pageName))->build();
    }

    public function createBottomNavigationLinks()
    {
        $bottomPanel = new BottomPanel();
        $bottomPanelDom = $bottomPanel->getDom();
        //TagUtils::createShadow($bottomPanelDom);
        return $bottomPanelDom;
    }

    public function createTree()
    {
        if ($this->isTreeVisible) {
            $treeView = new TreeView();
            return $treeView->createTree($this->getTreeKey());
        }
        return "";
    }

    public function createPathLinks()
    {
        /*        $Categories = new CategoriesComponent();
                return $Categories->build();*/
        return '';
    }

    protected function createGeneralContent()
    {
    }

    public function setIsStatusBarVisible($isStatusBarVisible)
    {
        $this->isStatusBarVisible = $isStatusBarVisible;
    }

    public function getIsStatusBarVisible()
    {
        return $this->isStatusBarVisible;
    }

    public function setIsTreeVisible($isTreeVisible)
    {
        $this->isTreeVisible = $isTreeVisible;
    }

    public function getIsTreeVisible()
    {
        return $this->isTreeVisible;
    }

    public function setTreeKey($treeKey)
    {
        $this->treeKey = $treeKey;
    }

    public function getTreeKey()
    {
        return $this->treeKey;
    }

    public function setPathLinkForTree($pathLinkForTree)
    {
        $this->pathLinkForTree = $pathLinkForTree;
    }

    public function getPathLinkForTree()
    {
        return $this->pathLinkForTree;
    }

    public function setPathLinkForMainBlock($pathLinkForMainBlock)
    {
        $this->pathLinkForMainBlock = $pathLinkForMainBlock;
    }

    public function getPathLinkForMainBlock()
    {
        return $this->pathLinkForMainBlock;
    }

    public function setIsViewModeBlockVisible($isViewModeBlockVisible)
    {
        $this->isViewModeBlockVisible = $isViewModeBlockVisible;
    }

    public function getIsViewModeBlockVisible()
    {
        return $this->isViewModeBlockVisible;
    }

    public function setViewModeBlock($viewModeBlock)
    {
        $this->viewModeBlock = $viewModeBlock;
    }

    public function getViewModeBlock()
    {
        return $this->viewModeBlock;
    }

    public function getPreBottom()
    {
        return '';
    }

    /**
     * @param boolean $isTopNavigationLinksFillingWidth
     */
    public function setIsTopNavigationLinksFillingWidth($isTopNavigationLinksFillingWidth)
    {
        $this->isTopNavigationLinksFillingWidth = $isTopNavigationLinksFillingWidth;
    }

    /**
     * @return boolean
     */
    public function getIsTopNavigationLinksFillingWidth()
    {
        return $this->isTopNavigationLinksFillingWidth;
    }

    /**
     * @param boolean $isBottomNavigationLinksFillingWidth
     */
    public function setIsBottomNavigationLinksFillingWidth($isBottomNavigationLinksFillingWidth)
    {
        $this->isBottomNavigationLinksFillingWidth = $isBottomNavigationLinksFillingWidth;
    }

    /**
     * @return boolean
     */
    public function getIsBottomNavigationLinksFillingWidth()
    {
        return $this->isBottomNavigationLinksFillingWidth;
    }

    /**
     * @param boolean $isPathLinkVisible
     */
    public function setIsPathLinkVisible($isPathLinkVisible)
    {
        $this->isPathLinkVisible = $isPathLinkVisible;
    }

    /**
     * @return boolean
     */
    public function getIsPathLinkVisible()
    {
        return $this->isPathLinkVisible;
    }

    /**
     * @param mixed $pageCode
     */
    public function setPageCode($pageCode)
    {
        $this->pageCode = $pageCode;
    }

    /**
     * @return mixed
     */
    public function getPageCode()
    {
        return $this->pageCode;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title . $this->permanentTitle;
    }

    /**
     * @param mixed $titleTag
     */
    public function setTitleTag($titleTag)
    {
        $this->titleTag = $titleTag;
    }

    public function updateTitleTagChildren($children)
    {
        if (!$this->titleTag) {
            $this->titleTag = new Title();
        }
        array_push($children, $this->permanentTitle);
        $this->titleTag->replaceChildren($children);
        return $this->titleTag;
    }

    /**
     * @return mixed
     */
    public function getTitleTag()
    {
        if (!$this->titleTag) {
            $this->updateTitleTagChildren([]);
        }
        return $this->titleTag;
    }

    public function addMetaTags()
    {
        $argsCount = func_num_args();
        for ($argIndex = 0; $argIndex < $argsCount; $argIndex++) {
            $tag = func_get_arg($argIndex);
            if ($tag instanceof Tag || is_string($tag) || is_numeric($tag)) {
                array_push($this->metaTags, $tag);
            }
        }
    }

    protected function getSourceScripts()
    {
        if ($this->isJsUglify) {
            return
                '<script type="text/javascript" src="/dist/vendor1.js"></script>
            <script type="text/javascript" src="/dist/vendor2.js"></script>
            <script type="text/javascript" src="/dist/bundle1.js"></script>
            <script type="text/javascript" src="/dist/bundle2.js"></script>';
        }
        return '
            <script type="text/javascript" src="/src/front/js/ext/jquery-2.1.4.min.js"></script>
            <script type="text/javascript" src="/src/front/js/components/core/keyboard/keyboard.component.js"></script>
            <script type="text/javascript" src="/src/front/js/components/search-input/search-input.component.js"></script>
            <script type="text/javascript" src="/src/front/js/components/top-menu/top-menu.component.js"></script>
            <script type="text/javascript" src="/src/front/js/components/email-form/email-form.component.js"></script>
            <script type="text/javascript" src="/node_modules/mustache/mustache.min.js"></script>
            <script type="text/javascript" src="/src/front/js/utils.js"></script>
        ';
    }
}