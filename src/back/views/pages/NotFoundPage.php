<?php
include_once AuWebRoot.'/src/back/import/db.php';
include_once AuWebRoot.'/src/back/import/pages.php';

class NotFoundPage extends AbstractPage
{
    private $search_value = "";

    public function __construct()
    {
        parent::__construct(UrlParameters::PAGE__MAIN);
        $this->setPageCode("search_page");
        $this->setIsStatusBarVisible(true);
        $this->setIsViewModeBlockVisible(true);
        $this->setIsTreeVisible(true);
        $this->setPathLinkForTree(PathLinks::getDOMForTree());
        $this->setViewModeBlock(PathLinks::getDOMForViewModeSelector());

        $this->content = $this->getHtml();
    }

    protected function createGeneralContent()
    {
        $container = new Div();
        $label404 = new Div();
        $label404->addStyleClass('page-not-found-code');
        $labelText = new Div();
        $labelText->addChild('Ошибка 404');
        $labelText->addStyleClass('page-not-found-code-text');
        $labelIcon = new Div();
        $labelIcon->addStyleClass('page-not-found-code-icon');
        $labelIcon->addChild('
            <svg xmlns="http://www.w3.org/2000/svg" fill="#000000" height="50" viewBox="0 0 24 24" width="50">
                <path d="M0 0h24v24H0z" fill="none"/>
                <circle cx="15.5" cy="9.5" r="1.5"/>
                <circle cx="8.5" cy="9.5" r="1.5"/>
                <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm0-6c-2.33 0-4.32 1.45-5.12 3.5h1.67c.69-1.19 1.97-2 3.45-2s2.75.81 3.45 2h1.67c-.8-2.05-2.79-3.5-5.12-3.5z"/>
            </svg>');
        $label404->addChildren($labelText, $labelIcon);

        $description = new Div();
        $description->addStyleClass('page-not-found-description');
        $description->addChild("Запрашиваемой страницы никогда не было на нашем сайте или она была удалена.<br> Если вы не согласны, 
        <a href='mailto:augustova@mail.ru?subject=Не нашли нужную страницу на augustova.by'>напишите нам</a> - будем разбираться.");
        return $container->addChildren($label404, $description, $this->getCatalog());
    }

    private function getCatalog()
    {
        $container = new Div();
        $container->addStyleClass('page-not-found-catalog');
        $text = new Span();
        $text->addStyleClass('page-not-found-catalog-label');
        $text->addChild('У нас много всего интересного в ');
        $link = new A();
        $link->addStyleClass('page-not-found-catalog-link');
        $link->addAttribute('href', Labels::$TOP_NAVIGATION_LINKS['catalog']);
        $link->addChild('каталоге.');
        return $container->addChildren($text, $link);
    }
}
