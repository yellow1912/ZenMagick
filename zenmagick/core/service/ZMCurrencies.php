<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
?>
<?php


/**
 * Currencies.
 *
 * @author mano
 * @package org.zenmagick.service
 * @version $Id$
 */
class ZMCurrencies extends ZMObject {
    var $currencies_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();

        $this->currencies_ = array();
        $this->_load();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return parent::instance('Currencies');
    }


    /**
     * Load all currencies.
     */
    function _load() {
        $db = ZMRuntime::getDB();
        $sql = "select code, title, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places, value
                from " . TABLE_CURRENCIES;

        $db = ZMRuntime::getDB();
        $results = $db->Execute($sql);

        while (!$results->EOF) {
            $currency = $this->_newCurrency($results->fields);
            $this->currencies_[$currency->getId()] = $currency;
            $results->MoveNext();
        }

    }

    /**
     * Get all currencies.
     *
     * @return array A list of <code>ZMCurrency</code> objects.
     */
    function getCurrencies() { return $this->currencies_; }

    /**
     * Get the currency for the given code.
     *
     * @param string code The currency code.
     * @return ZMCurrency A currency or <code>null</code>.
     */
    function getCurrencyForCode($code) { return isset($this->currencies_[$code]) ? $this->currencies_[$code] : null; }

    /**
     * Checks if a currency exists for the given code.
     *
     * @param string code The currency code.
     * @return boolean <code>true</code> if a currency exists for the given code, <code>false</code> if not.
     */
    function isValid($code) {
        return null !== $this->getCurrencyForId($code);
    }

    /**
     * Create new currency instance.
     */
    function _newCurrency($fields) {
        $currency = ZMLoader::make("Currency");
        $currency->code_ = $fields['code'];
        $currency->name_ = $fields['title'];
        $currency->symbolLeft_ = $fields['symbol_left'];
        $currency->symbolRight_ = $fields['symbol_right'];
        $currency->decimalPoint_ = $fields['decimal_point'];
        $currency->thousandsPoint_ = $fields['thousands_point'];
        $currency->decimalPlaces_ = $fields['decimal_places'];
        $currency->rate_ = $fields['value'];
        return $currency;
    }

}

?>
