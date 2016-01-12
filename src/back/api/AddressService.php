<?php
/**
 * Created by PhpStorm.
 * User: vinni
 * Date: 10/31/14
 * Time: 12:19 AM
 */

class AddressService {

    public static function getAddresses() {
        $addressType = new DBAddressType();
        return $addressType->getActiveAddresses();
    }

} 