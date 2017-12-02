<?php
include_once('src/back/import/import');
include_once('src/back/import/db');

class Dao
{
    const NEWS = [
        "id" => DB::TABLE_NEWS__ID,
        DB::TABLE_NEWS__TITLE => DB::TABLE_NEWS__TITLE,
        DB::TABLE_NEWS__TYPE => DB::TABLE_NEWS__TYPE,
        DB::TABLE_NEWS__TEXT => DB::TABLE_NEWS__TEXT,
        DB::TABLE_NEWS__VIDEO_TYPE => DB::TABLE_NEWS__VIDEO_TYPE,
        DB::TABLE_NEWS__VIDEO_URL => DB::TABLE_NEWS__VIDEO_URL,
        DB::TABLE_NEWS__CONTENT => DB::TABLE_NEWS__CONTENT,
        DB::TABLE_NEWS__CREATING_TIME => DB::TABLE_NEWS__CREATING_TIME
    ];

}