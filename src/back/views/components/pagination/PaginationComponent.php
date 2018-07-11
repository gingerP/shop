<?php

include_once AuWebRoot . '/src/back/views/components/AbstractComponent.php';

class PaginationComponent extends AbstractComponent
{
    private $groupSize;

    function __construct($groupSize = 2)
    {
        parent::__construct();
        $this->groupSize = $groupSize;
    }

    public function buildForCategory($categoryCode, $currentPage, $pageSize, $totalCount)
    {
        $tpl = parent::getEngine()->loadTemplate('components/pagination/pagination.mustache');
        $paginationItems = $this->getPaginationItemsForCategory($categoryCode, $currentPage, $pageSize, $totalCount);
        return $tpl->render([
            'pagination' => $paginationItems,
            'i18n' => Localization
        ]);
    }

    private function getPaginationItemsForCategory($categoryCode, $currentPage, $pageSize, $totalCount)
    {
        $dots = false;
        $paginationItems = [];
        if ($totalCount != 0 && $totalCount >= $pageSize) {
            $amountPages = ceil($totalCount / $pageSize);
            if ($currentPage > 0 && $currentPage <= $amountPages) {
                if ($currentPage != 1) {
                    $paginationItems[] = [
                        'isPrevious' => true,
                        'url' => URLBuilder::getCatalogLinkPrevForCategory($categoryCode, $currentPage, $pageSize)
                    ];
                }

                for ($page = 1; $page <= $amountPages; $page++) {
                    if (
                        $page < 2
                        || ($page > $currentPage - $this->groupSize && $page < $currentPage + $this->groupSize)
                        || ($page > $amountPages - 1)
                    ) {
                        $dots = false;
                        if ($page != $currentPage) {
                            $paginationItems[] = [
                                'isNumeric' => true,
                                'url' => URLBuilder::getCatalogLinkForCategory($categoryCode, $page, $pageSize),
                                'label' => $page
                            ];
                        } else {
                            $paginationItems[] = [
                                'isEmpty' => true,
                                'label' => $page
                            ];
                        }
                    } else if (!$dots) {
                        $dots = true;
                        $paginationItems[] = [
                            'isDots' => true,
                            'url' => ''
                        ];
                    }
                }

                if ($currentPage != $amountPages) {
                    $paginationItems[] = [
                        'isNext' => true,
                        'url' => URLBuilder::getCatalogLinkNextForCategory($categoryCode, $currentPage, $pageSize)
                    ];
                }
            }
        }
        return $paginationItems;
    }

}