<?php

include_once AuWebRoot . '/src/back/import/import.php';
include_once AuWebRoot . '/src/back/import/pages.php';
include_once AuWebRoot . '/src/back/import/tags.php';
include_once AuWebRoot . '/src/back/views/components/topPanel/TopPanelComponent.php';
include_once AuWebRoot . '/src/back/views/components/categories/CategoriesComponent.php';

use Katzgrau\KLogger\Logger as Logger;

abstract class AbstractPage
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
                $this->getCopyright(),
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
        $head->addChild('<style type="text/css">' . file_get_contents(AuWebRoot . '/dist/style.css') . ' </style> ');
        $head->addChild('
            <link rel = "manifest" href = "/manifest.webmanifest">            
            <link rel = "apple-touch-icon" href = "/images/system/favicon-200.png">
            <meta name = "apple-mobile-web-app-capable" content = "yes">
            <meta name = "apple-mobile-web-app-status-bar-style" content = "black">
            <link rel = "shortcut icon" href = "/images/system/favicon.png" type = "image/x-icon" />');
        $head->addChild(SearchEngines::getGoogleAnalyticScript());
        $head->addChild(SearchEngines::getYandexMetricScript());
        return $head;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getCopyright()
    {
        return '<div id = "copyright" > ' . Localization['copyright'] . ' </div > ';
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

    public function updateTitleTagChildren($title)
    {
        if (!$this->titleTag) {
            $this->titleTag = new Title();
        }
        if (is_null($title) || $title == '') {
            $this->permanentTitle = [];
            $this->titleTag->replaceChildren([Localization['company.name.short']]);
        } else {
            $this->permanentTitle = [$title . ' - '];
            $this->titleTag->replaceChildren([$title . ' - ' . Localization['company.name.short']]);
        }
        return $this->titleTag;
    }

    /**
     * @return mixed
     */
    public function getTitleTag()
    {
        if (!$this->titleTag) {
            $this->updateTitleTagChildren('');
        }
        return $this->titleTag;
    }

    public function addMetaTags()
    {
        $argsCount = func_num_args();
        for ($argIndex = 0; $argIndex < $argsCount; $argIndex++) {
            $tag = func_get_arg($argIndex);
            if ($tag instanceof Tag || is_string($tag) || is_numeric($tag)) {
                $this->metaTags[] = $tag;
            }
        }
    }

    protected function getSourceScripts()
    {
        if ($this->isJsUglify) {
            return
                '<script type="text/javascript">var _extends=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var n=arguments[t];for(var o in n)Object.prototype.hasOwnProperty.call(n,o)&&(e[o]=n[o])}return e},_typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e};!function(e,t){"object"===("undefined"==typeof exports?"undefined":_typeof(exports))&&"undefined"!=typeof module?module.exports=t():"function"==typeof define&&define.amd?define(t):e.LazyLoad=t()}(this,function(){"use strict";var e=function(){return{elements_selector:"img",container:window,threshold:300,throttle:150,data_src:"src",data_srcset:"srcset",class_loading:"loading",class_loaded:"loaded",class_error:"error",class_initial:"initial",skip_invisible:!0,callback_load:null,callback_error:null,callback_set:null,callback_processed:null,callback_enter:null}},t=!("onscroll"in window)||/glebot/.test(navigator.userAgent),n=function(e,t){e&&e(t)},o=function(e){return e.getBoundingClientRect().top+window.pageYOffset-e.ownerDocument.documentElement.clientTop},i=function(e,t,n){return(t===window?window.innerHeight+window.pageYOffset:o(t)+t.offsetHeight)<=o(e)-n},s=function(e){return e.getBoundingClientRect().left+window.pageXOffset-e.ownerDocument.documentElement.clientLeft},r=function(e,t,n){var o=window.innerWidth;return(t===window?o+window.pageXOffset:s(t)+o)<=s(e)-n},l=function(e,t,n){return(t===window?window.pageYOffset:o(t))>=o(e)+n+e.offsetHeight},a=function(e,t,n){return(t===window?window.pageXOffset:s(t))>=s(e)+n+e.offsetWidth},c=function(e,t,n){return!(i(e,t,n)||l(e,t,n)||r(e,t,n)||a(e,t,n))},u=function(e,t){var n,o=new e(t);try{n=new CustomEvent("LazyLoad::Initialized",{detail:{instance:o}})}catch(e){(n=document.createEvent("CustomEvent")).initCustomEvent("LazyLoad::Initialized",!1,!1,{instance:o})}window.dispatchEvent(n)},d=function(e,t){return e.getAttribute("data-"+t)},h=function(e,t,n){return e.setAttribute("data-"+t,n)},f=function(e,t){var n=e.parentNode;if(!n||"PICTURE"===n.tagName)for(var o=0;o<n.children.length;o++){var i=n.children[o];if("SOURCE"===i.tagName){var s=d(i,t);s&&i.setAttribute("srcset",s)}}},_=function(e,t,n){var o=e.tagName,i=d(e,n);if("IMG"===o){f(e,t);var s=d(e,t);return s&&e.setAttribute("srcset",s),void(i&&e.setAttribute("src",i))}"IFRAME"!==o?i&&(e.style.backgroundImage=\'url("\'+i+\'")\'):i&&e.setAttribute("src",i)},p="undefined"!=typeof window,m=p&&"classList"in document.createElement("p"),g=function(e,t){m?e.classList.add(t):e.className+=(e.className?" ":"")+t},v=function(e,t){m?e.classList.remove(t):e.className=e.className.replace(new RegExp("(^|\\s+)"+t+"(\\s+|$)")," ").replace(/^\s+/,"").replace(/\s+$/,"")},w=function(t){this._settings=_extends({},e(),t),this._queryOriginNode=this._settings.container===window?document:this._settings.container,this._previousLoopTime=0,this._loopTimeout=null,this._boundHandleScroll=this.handleScroll.bind(this),this._isFirstLoop=!0,window.addEventListener("resize",this._boundHandleScroll),this.update()};w.prototype={_reveal:function(e){var t=this._settings,o=function o(){t&&(e.removeEventListener("load",i),e.removeEventListener("error",o),v(e,t.class_loading),g(e,t.class_error),n(t.callback_error,e))},i=function i(){t&&(v(e,t.class_loading),g(e,t.class_loaded),e.removeEventListener("load",i),e.removeEventListener("error",o),n(t.callback_load,e))};n(t.callback_enter,e),"IMG"!==e.tagName&&"IFRAME"!==e.tagName||(e.addEventListener("load",i),e.addEventListener("error",o),g(e,t.class_loading)),_(e,t.data_srcset,t.data_src),n(t.callback_set,e)},_loopThroughElements:function(){var e=this._settings,o=this._elements,i=o?o.length:0,s=void 0,r=[],l=this._isFirstLoop;for(s=0;s<i;s++){var a=o[s];e.skip_invisible&&null===a.offsetParent||(t||c(a,e.container,e.threshold))&&(l&&g(a,e.class_initial),this._reveal(a),r.push(s),h(a,"was-processed",!0))}for(;r.length;)o.splice(r.pop(),1),n(e.callback_processed,o.length);0===i&&this._stopScrollHandler(),l&&(this._isFirstLoop=!1)},_purgeElements:function(){var e=this._elements,t=e.length,n=void 0,o=[];for(n=0;n<t;n++){var i=e[n];d(i,"was-processed")&&o.push(n)}for(;o.length>0;)e.splice(o.pop(),1)},_startScrollHandler:function(){this._isHandlingScroll||(this._isHandlingScroll=!0,this._settings.container.addEventListener("scroll",this._boundHandleScroll))},_stopScrollHandler:function(){this._isHandlingScroll&&(this._isHandlingScroll=!1,this._settings.container.removeEventListener("scroll",this._boundHandleScroll))},handleScroll:function(){var e=this._settings.throttle;if(0!==e){var t=Date.now(),n=e-(t-this._previousLoopTime);n<=0||n>e?(this._loopTimeout&&(clearTimeout(this._loopTimeout),this._loopTimeout=null),this._previousLoopTime=t,this._loopThroughElements()):this._loopTimeout||(this._loopTimeout=setTimeout(function(){this._previousLoopTime=Date.now(),this._loopTimeout=null,this._loopThroughElements()}.bind(this),n))}else this._loopThroughElements()},update:function(){this._elements=Array.prototype.slice.call(this._queryOriginNode.querySelectorAll(this._settings.elements_selector)),this._purgeElements(),this._loopThroughElements(),this._startScrollHandler()},destroy:function(){window.removeEventListener("resize",this._boundHandleScroll),this._loopTimeout&&(clearTimeout(this._loopTimeout),this._loopTimeout=null),this._stopScrollHandler(),this._elements=null,this._queryOriginNode=null,this._settings=null}};var b=window.lazyLoadOptions;return p&&b&&function(e,t){var n=t.length;if(n)for(var o=0;o<n;o++)u(e,t[o]);else u(e,t)}(w,b),w});</script>
            <script > new LazyLoad();</script > ';
        }
        return '
            <script type="text/javascript" src="/src/front/js/ext/jquery-2.1.4.min.js"></script>
            <script type="text/javascript" src="/src/front/js/components/core/keyboard/keyboard.component.js"></script>
            <script type="text/javascript" src="/src/front/js/components/search-input/search-input.component.js"></script>
            <script type="text/javascript" src="/src/front/js/components/top-menu/top-menu.component.js"></script>
            <script type="text/javascript" src="/src/front/js/components/email-form/email-form.component.js"></script>
            <script type="text/javascript" src="/node_modules/mustache/mustache.min.js"></script>
            <script type="text/javascript" src="/src/front/js/utils.js"></script>
            <script type="text/javascript" src="/src/front/js/components/vk/index.js"></script>
            <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-lazyload/8.7.1/lazyload.min.js"></script>
            <script > new LazyLoad();</script >
    ';
    }
}