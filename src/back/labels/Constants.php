<?php

class Constants {
    const SESSION_LIFE_TIME = 1000;
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

    const DEFAULT_FIRST_IMAGE_PREFIX = '001';
    const DEFAULT_ITEM_DESCRIPTION_KEY = 'k_main';
    const DEFAULT_HOME_PAGE_BIG_VIEW_GALLERY_ITEM = "SC";
    const HIGH_LIGHT_ELEMENT = 'high_light_element';
    const LIST_DELIMITER = ";";
    const MAX_IMAGE_COUNT_METRO_VIEW = 5;
    const FEEDBACK_MAIL = "FEEDBACK_EMAIL";
    const CATALOG_PATH = "CATALOG_PATH";
    const SYSTEM_MAIL = "SYSTEM_EMAIL";
    const WATERMARK_MEDIUM_PATH = "WATERMARK_MEDIUM_PATH";
    const WATERMARK_LARGE_PATH = "WATERMARK_LARGE_PATH";
    const PRICE_DIRECTORY = "PRICE_DIRECTORY";
    const BOOKLET_IMAGE_PATH = "BOOKLET_IMAGE_PATH";
    const TEMP_DIRECTORY = "TEMP_DIRECTORY";
    const BOOKLET_BACKGROUND_IMAGES_PATH = "BOOKLET_BACKGROUND_IMAGES_PATH";
}