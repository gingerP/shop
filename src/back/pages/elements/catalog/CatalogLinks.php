<?php
include_once("import");
include_once("db");
include_once("page");

class CatalogLinks {
    private $linksGroupCount = 3;

    public function getPaginationLinks($pageNumber, $num, $totalCount, $topBottom) {
        $mainTag = new Div();
        $catalogLink = new CatalogLink();
        $dots = false;
        $topBottomStyle = $topBottom == 'bottom' ? 'link_next_prev_bottom' : '';
        if ($totalCount != 0) {
            $amountPages = ceil($totalCount / ($num));
            if ($pageNumber > 0 && $pageNumber <= $amountPages) {
                $mainTag->addStyleClasses(["pagination_bar", "right_top_bar", $topBottomStyle]);
                $brokerTag = new Div();
                $mainTag->addChild($brokerTag);
                $tagCenterContainer = new Span();
                if ($pageNumber != 1) {
                    $tagPrevious = new A();
                    $tagPrevious->addStyleClasses(["f-16", "text_non_select", "link_style", "link_next_prev", "input_hover", "prev_link"]);
                    $tagPrevious->addAttribute("href",URLBuilder::getCatalogLinkPrev($pageNumber, $num));
                    $text = new Div();
                    $text->addStyleClass("text");
                    $text->addChild("назад");
                    $arrow = new Div();
                    $arrow->addStyleClass("arrow");
                    $tagCenterContainer->addChild($tagPrevious->addChildList([$arrow, $text]));
                }

                $brokerTag->addChild($tagCenterContainer);
                $tagCenterContainer->addStyleClasses(["numeric_links", "f-15"]);
                for ($currentRenderPage = 1; $currentRenderPage <= $amountPages; $currentRenderPage++) {
                    if ($currentRenderPage < 2
                        || ($currentRenderPage > $pageNumber - $this->linksGroupCount && $currentRenderPage < $pageNumber + $this->linksGroupCount)
                        || ($currentRenderPage > $amountPages - 1)) {
                        $dots = false;
                        if ($currentRenderPage != $pageNumber) {
                            $tagCenterContainer->addChild($catalogLink->getLink($currentRenderPage, $num));
                        } else {
                            $emptyLinkView = $catalogLink->getEmptyLink($pageNumber);
                            $emptyLinkView->addStyleClass("f-16");
                            $tagCenterContainer->addChild($emptyLinkView);
                        }
                    } else if (!$dots){
                        $dots = true;
                        $tagCenterContainer->addChild($catalogLink->get3dots());
                    }
                }

                if ($pageNumber != $amountPages) {
                    $tagNext = new A();
                    $tagNext->addStyleClasses(["f-16", "text_non_select", "link_style", "input_hover", "link_next_prev", "next_link"]);
                    $tagNext->addAttribute("href", URLBuilder::getCatalogLinkNext($pageNumber, $num));
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
