<?php

class Constants
{
    const SESSION_LIFE_TIME = 60 * 20; //10 min
    const SESSION_ID_LIFETIME = 1;
    const SESSION_LAST_ACTIVITY = "SESSION_LAST_ACTIVITY";
    const SESSION_START_TIME = "SESSION_START_TIME";
    const SESSION_USER_IP = "SESSION_USER_IP";

    const LOGIN_USER = "user";
    const LOGIN_PASSWORD = "password";

    const DEFAULT_TEXT_LENGTH_FOR_CATALOG_PATH_LINK = 40;
    const DEFAULT_PAGE_NUMBER = 1;
    const DEFAULT_ITEM_COUNT_PER_PAGE = 48;
    const DEFAULT_ITEM_IMAGE = 'images/blueberries.jpg';
    const DEFAULT_ROOT_CATALOG_PATH = 'images/catalog';
    const DEFAULT_ROOT_PRICE_PATH = 'prices/';

    const SMALL_IMAGE = 's_';
    const SMALL_IMAGE_WIDTH = 130;
    const SMALL_IMAGE_HEIGHT = 220;

    const MEDIUM_IMAGE = 'm_';
    const MEDIUM_IMAGE_WIDTH = 420;
    const MEDIUM_IMAGE_HEIGHT = 700;

    const LARGE_IMAGE = 'l_';
    const LARGE_IMAGE_WIDTH = 1200;
    const LARGE_IMAGE_HEIGHT = 2000;
    static $BOOKLET_IMAGE_SIZE_X1 = [700, 1000];
    static $BOOKLET_IMAGE_SIZE_X2 = [700, 1000];
    static $BOOKLET_IMAGE_SIZE_X3 = [700, 1000];
}