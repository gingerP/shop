<?php
include_once("src/back/import/import");
include_once("src/back/import/db");
include_once("src/back/import/page");
use Katzgrau\KLogger\Logger as Logger;

class CatalogLinks
{
    private $linksGroupCount = 2;

    public function __construct()
    {
        $this->logger = new Logger(AU_CONFIG['log.file'], AU_CONFIG['log.level']);
    }

    /**
     * @param array $params Array containing the necessary params.
     *    $params = [
     *      'pageNum' => (int) DB hostname. Required.
     *      'itemsCount' => (int) DB name. Required.
     *      'totalCount' => (int) DB username. Required.
     *      'category' => (string)(optional) DB password. Required.
     *      'position' => (string) DB port. Default: 1433.
     *      'searchValue' => (string)(optional)
     *    ]
     * @return Tag
     */

    public function getPaginationLinks($params = [])
    {
        $mainTag = new Div();
        $catalogLink = new CatalogLink();
        $dots = false;
        $topBottomStyle = $params['position'] == 'bottom' ? 'link_next_prev_bottom' : '';
        $totalCount = $params['totalCount'];
        $itemsCount = $params['itemsCount'];
        if ($totalCount != 0) {
            $amountPages = ceil($totalCount / $itemsCount);
            $pageNumber = $params['pageNum'];
            $itemsCount = $params['itemsCount'];
            if ($pageNumber > 0 && $pageNumber <= $amountPages) {
                $mainTag->addStyleClasses(['pagination_bar', 'right_top_bar', $topBottomStyle]);
                $brokerTag = new Div();
                $mainTag->addChild($brokerTag);
                $tagCenterContainer = new Span();
                if ($pageNumber != 1) {
                    $tagPrevious = new A();
                    $tagPrevious->addStyleClass(
                        'f-16 text_non_select link_style link_next_prev input_hover prev_link pagination-item'
                    );

                    if (array_key_exists('category', $params)) {
                        $tagPrevious->addAttribute('href',
                            URLBuilder::getCatalogLinkPrevForCategory($params['category'], $pageNumber, $itemsCount)
                        );
                    } else if (array_key_exists('searchValue', $params)) {
                        $tagPrevious->addAttribute('href',
                            URLBuilder::getCatalogLinkPrevForSearch($params['searchValue'], $pageNumber, $itemsCount)
                        );
                    } else {
                        $tagPrevious->addAttribute('href',
                            URLBuilder::getCatalogLinkPrev($pageNumber, $itemsCount)
                        );
                    }

                    $text = new Div();
                    $text->addStyleClass('text');
                    $text->addChild('назад');
                    $arrow = new Div();
                    $arrow->addStyleClass('arrow');
                    $tagCenterContainer->addChild($tagPrevious->addChildList([$arrow, $text]));
                }

                $brokerTag->addChild($tagCenterContainer);
                $tagCenterContainer->addStyleClass('numeric_links f-15');
                for ($currentRenderPage = 1; $currentRenderPage <= $amountPages; $currentRenderPage++) {
                    if ($currentRenderPage < 2
                        || ($currentRenderPage > $pageNumber - $this->linksGroupCount
                            && $currentRenderPage < $pageNumber + $this->linksGroupCount)
                        || ($currentRenderPage > $amountPages - 1)
                    ) {
                        $dots = false;
                        if ($currentRenderPage != $pageNumber) {

                            if (array_key_exists('category', $params)) {
                                $tagCenterContainer->addChild(
                                    $catalogLink->getLinkForCategory(
                                        $params['category'], $currentRenderPage, $itemsCount
                                    )
                                );
                            } else if (array_key_exists('searchValue', $params)) {
                                $tagCenterContainer->addChild(
                                    $catalogLink->getLinkForSearch(
                                        $params['searchValue'], $currentRenderPage, $itemsCount
                                    )
                                );
                            } else {
                                $tagCenterContainer->addChild($catalogLink->getLink($currentRenderPage, $itemsCount));
                            }
                        } else {
                            $emptyLinkView = $catalogLink->getEmptyLink($pageNumber);
                            $emptyLinkView->addStyleClass("f-16");
                            $tagCenterContainer->addChild($emptyLinkView);
                        }
                    } else if (!$dots) {
                        $dots = true;
                        $tagCenterContainer->addChild($catalogLink->get3dots());
                    }
                }

                if ($pageNumber != $amountPages) {
                    $tagNext = new A();
                    $tagNext->addStyleClass(
                        'f-16 text_non_select link_style input_hover link_next_prev next_link pagination-item'
                    );

                    if (array_key_exists('category', $params)) {
                        $tagNext->addAttribute('href',
                            URLBuilder::getCatalogLinkNextForCategory($params['category'], $pageNumber, $itemsCount)
                        );
                    } else if (array_key_exists('searchValue', $params)) {
                        $tagNext->addAttribute('href',
                            URLBuilder::getCatalogLinkNextForSearch($params['searchValue'], $pageNumber, $itemsCount)
                        );
                    } else {
                        $tagNext->addAttribute('href',
                            URLBuilder::getCatalogLinkNext($pageNumber, $itemsCount)
                        );
                    }

                    $text = new Div();
                    $text->addStyleClass("text");
                    $text->addChild("вперед");
                    $arrow = new Div();
                    $arrow->addStyleClass("arrow");
                    $tagCenterContainer->addChild($tagNext->addChildList([$text, $arrow]));
                }
            }
        }
        return $mainTag;
    }

}
