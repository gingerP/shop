<?php
include_once('db');
include_once('import');
class NewsService {

    public static function loadNews($page, $offset) {
        $page = number_format($page);
        $offset = number_format($offset);
        if ($page != "" && $offset != "") {
            $dbNews = new DBNewsType();
            $news = $dbNews->executeRequestWithLimit('', '', DB::TABLE_NEWS__CREATING_TIME, DB::DESC, $page * $offset, $offset);
            return $dbNews->extractDataFromResponse($news, Dao::NEWS);
        }
        return [];
    }
}