<?php

class DB {

    const TABLE_ORDER = 'id';
    const TABLE_ACTIVE = 'active';

    const ASC = 'ASC';
    const DESC = 'DESC';

    const TABLE_NAV_KEY___NAME = 'nav_key';
    const TABLE_NAV_KEY__ID = 'id';
    const TABLE_NAV_KEY__VALUE = 'value';
    const TABLE_NAV_KEY__IMAGE = 'image';
    const TABLE_NAV_KEY__KEY_ITEM = 'key_item';
    const TABLE_NAV_KEY__PARENT_KEY = 'parent_key';
    const TABLE_NAV_KEY__HOME_VIEW = 'home_view';
    const TABLE_NAV_KEY___ORDER = self::TABLE_NAV_KEY__ID;

    const TABLE_USER_ORDER___NAME = 'user_order';
    const TABLE_USER_ORDER___ORDER = self::TABLE_USER_ORDER__ID;
    const TABLE_USER_ORDER__ID = 'user_order_id';
    const TABLE_USER_ORDER__GOOD_ID = 'good_id';
    const TABLE_USER_ORDER__GOOD_INDEX = 'good_index';

    const TABLE_GOODS___NAME = 'goods';
    const TABLE_GOODS___ORDER = 'name';
    const TABLE_GOODS__ID = 'id';
    const TABLE_GOODS__KEY_ITEM = 'key_item';
    const TABLE_GOODS__NAME = 'name';
    const TABLE_GOODS__CATEGORY = 'category';
    const TABLE_GOODS__DESCRIPTION = 'description';
    const TABLE_GOODS__IMAGE_PATH = 'image_path';
    const TABLE_GOODS__VERSION = 'version';
    const TABLE_GOODS__IMAGES = 'images';

    const TABLE_ADDRESS___NAME = 'address';
    const TABLE_ADDRESS__ID = 'address_id';
    const TABLE_ADDRESS__STATUS = 'status';
    const TABLE_ADDRESS__DESCRIPTION = 'description';
    const TABLE_ADDRESS__PRICES = 'prices';
    const TABLE_ADDRESS__MAP = 'map';
    const TABLE_ADDRESS__MOBILE_NUMBERS = 'mobile_numbers';
    const TABLE_ADDRESS__TITLE = 'title';
    const TABLE_ADDRESS__CITY = 'city';
    const TABLE_ADDRESS__COLOR = 'color';
    const TABLE_ADDRESS__ADDRESS = 'address';
    const TABLE_ADDRESS__TITLE_ADDRESS = 'title_address';
    const TABLE_ADDRESS__WEEKEND = 'weekend';
    const TABLE_ADDRESS__WORKING_HOURS = 'working_hours';
    const TABLE_ADDRESS__IMAGES = 'images';
    const TABLE_ADDRESS__ORDER = 'order';
    const TABLE_ADDRESS__EMAIL = 'email';
    const TABLE_ADDRESS___ORDER = self::TABLE_ADDRESS__ORDER;

    const TABLE_PAGES_CACHE___NAME = 'pages_cache';
    const TABLE_PAGES_CACHE__ID = 'pages_cache_id';
    const TABLE_PAGES_CACHE__HASH = 'hash';
    const TABLE_PAGES_CACHE__CONTENT = 'content';
    const TABLE_PAGES_CACHE___ORDER = self::TABLE_PAGES_CACHE__HASH;

    const TABLE_MAPS___NAME = 'maps';
    const TABLE_MAPS___ORDER = self::TABLE_MAPS__ID;
    const TABLE_MAPS__ID = 'map_id';
    const TABLE_MAPS__COORDINATES = 'address_id';
    const TABLE_MAPS__ZOOM = 'prices';

    const TABLE_PREFERENCES___NAME = 'preferences';
    const TABLE_PREFERENCES__ID = 'preferences_id';
    const TABLE_PREFERENCES___ORDER = self::TABLE_PREFERENCES__KEY;
    const TABLE_PREFERENCES__KEY = 'key';
    const TABLE_PREFERENCES__VALUE = 'value';
    const TABLE_PREFERENCES__VALUE_TYPE = 'value_type';
    const TABLE_PREFERENCES__DESCRIPTION = 'description';


    const TABLE_GOODS_TYPES___NAME = 'goods_types';
    const TABLE_GOODS_TYPES___ORDER = self::TABLE_GOODS_TYPES__ID;
    const TABLE_GOODS_TYPES__ID = 'goods_types_id';
    const TABLE_GOODS_TYPES__CODE = 'code';
    const TABLE_GOODS_TYPES__NAME = 'name';
    const TABLE_GOODS_TYPES__DESCRIPTION = 'description';
    const TABLE_GOODS_TYPES__PRICE_FILE_NAME = 'price_file_name';
    const TABLE_GOODS_TYPES__ABBREVIATION = 'abbreviation';

    const TABLE_SESSIONS___NAME = 'sessions';
    const TABLE_SESSIONS___ORDER = self::TABLE_SESSIONS__ID;
    const TABLE_SESSIONS__ID = 'session_id';
    const TABLE_SESSIONS__NAME = 'name';
    const TABLE_SESSIONS__CREATION_DATE = 'creation_date';
    const TABLE_SESSIONS__USER_ID = 'user_id';

    const TABLE_USERS___NAME = 'users';
    const TABLE_USERS___ORDER = self::TABLE_USERS__ID;
    const TABLE_USERS__ID = 'user_id';
    const TABLE_USERS__NAME = 'name';
    const TABLE_USERS__PASSWORD = 'password';

    const TABLE_ERRORS___NAME = 'errors';
    const TABLE_ERRORS___ORDER = self::TABLE_ERRORS___NAME;
    const TABLE_ERRORS__ID = 'id';
    const TABLE_ERRORS__NAME = 'name';
    const TABLE_ERRORS__MESSAGE = 'message';
    const TABLE_ERRORS__STACK = 'stack';
    const TABLE_ERRORS__DATE = 'date';

    const TABLE_NEWS___NAME = 'news';
    const TABLE_NEWS___ORDER = self::TABLE_NEWS__ID;
    const TABLE_NEWS__ID = 'news_id';
    const TABLE_NEWS__TITLE = 'title';
    const TABLE_NEWS__TYPE = 'type';
    const TABLE_NEWS__TEXT = 'text';
    const TABLE_NEWS__VIDEO_TYPE = 'video_type';
    const TABLE_NEWS__VIDEO_URL = 'video_url';
    const TABLE_NEWS__CONTENT = 'content';
    const TABLE_NEWS__CREATING_TIME = 'creation_time';
    const TABLE_NEWS__ACTIVE = self::TABLE_ACTIVE;

    const TABLE_BOOKLET___NAME = 'booklets';
    const TABLE_BOOKLET___ORDER = self::TABLE_BOOKLET__ID;
    const TABLE_BOOKLET__ID = 'booklet_id';
    const TABLE_BOOKLET__CODE = 'code';
    const TABLE_BOOKLET__CREATED = 'created';
    const TABLE_BOOKLET__UPDATED = 'updated';
    const TABLE_BOOKLET__DATA = 'data';
    const TABLE_BOOKLET__NAME = 'name';
    const TABLE_BOOKLET__ITEM_TYPE = 'itemType';

    const TABLE_ID = 'id';

    const TABLE_GOODS___MAPPER = [
    	self::TABLE_GOODS___NAME => self::TABLE_GOODS___NAME,
    	self::TABLE_GOODS___ORDER => self::TABLE_GOODS___ORDER,
    	self::TABLE_GOODS__ID => self::TABLE_GOODS__ID,
    	self::TABLE_GOODS__KEY_ITEM => self::TABLE_GOODS__KEY_ITEM,
    	self::TABLE_GOODS__NAME => self::TABLE_GOODS__NAME,
    	self::TABLE_GOODS__DESCRIPTION => self::TABLE_GOODS__DESCRIPTION,
    	self::TABLE_GOODS__IMAGE_PATH => self::TABLE_GOODS__IMAGE_PATH,
    	self::TABLE_GOODS__VERSION => self::TABLE_GOODS__VERSION
    ];

    const TABLE_NEWS__MAPPER = [
        self::TABLE_NEWS__ID => self::TABLE_NEWS__ID,
        self::TABLE_NEWS__TITLE => self::TABLE_NEWS__TITLE,
        self::TABLE_NEWS__TYPE => self::TABLE_NEWS__TYPE,
        self::TABLE_NEWS__TEXT => self::TABLE_NEWS__TEXT,
        self::TABLE_NEWS__VIDEO_TYPE => self::TABLE_NEWS__VIDEO_TYPE,
        self::TABLE_NEWS__VIDEO_URL => self::TABLE_NEWS__VIDEO_URL,
        self::TABLE_NEWS__CONTENT => self::TABLE_NEWS__CONTENT,
        self::TABLE_NEWS__CREATING_TIME => self::TABLE_NEWS__CREATING_TIME
    ];
}