<?php
include_once('src/back/import/import');
include_once('src/back/import/tag');

class AdminPagesCreator {

    private $pagePrefix = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">";

    public function getHtml() {
        $html = new Html();
        $head = new Head();
        $head->addChild("
        <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
        <meta http-equiv='cache-control' content='max-age=0' />
        <meta http-equiv='cache-control' content='no-cache' />
        <meta http-equiv='expires' content='0' />
        <meta http-equiv='expires' content='Tue, 01 Jan 1980 1:00:00 GMT' />
        <meta http-equiv='pragma' content='no-cache' />
        <link rel='stylesheet' type='text/css' href='/dist/style.css'>
        <link rel='stylesheet' type='text/css' href='/dist/admin-style.css'>
        ");
        $head->addChild(Components::getMenu());
        $head->addChildList($this->getHeadContent());
        $body = new Body();
        $body->addChildList($this->getGeneralContent());

        return $this->pagePrefix.$html->addChildList([$head, $body])->getHtml();
    }

    protected function getHeadContent() {}

    protected function getGeneralContent() {}

} 