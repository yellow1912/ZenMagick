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


define('ZM_TABLE_GROUP_PRICING', ZM_DB_PREFIX . 'zm_group_pricing');


/**
 * Service class for product based grou pricing
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.zm_group_pricing
 * @version $Id$
 */
class ProductGroupPricingService extends ZMObject {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        ZMDbTableMapper::instance()->setMappingForTable('zm_group_pricing', array(
            'id' => 'column=group_pricing_id;type=integer;key=true;auto=true',
            'productId' => 'column=products_id;type=integer',
            'groupId' => 'column=group_id;type=integer',
            'discount' => 'column=discount;type=float',
            'type' => 'column=type;type=string',
            'regularPriceOnly' => 'column=regular_price_only;type=boolean',
            'startDate' => 'column=start_date;type=datetime',
            'endDate' => 'column=end_date;type=datetime',
        ));
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
        return ZMObject::singleton('ProductGroupPricingService');
    }


    /**
     * Get product group pricing for the given product and group.
     *
     * @param int productId The source product id.
     * @param int groupId The group id.
     * @param boolean active If set to <code>true</code> consider active (date) pricings only; default is <code>true</code>.
     * return ProductGroupPricing A <code>ProductGroupPricing</code> instance or <code>null</code>.
     */
    function getProductGroupPricing($productId, $groupId, $active=true) {
        if ($active) {
            $dateLimit = ' AND start_date <= now() AND (end_date > now() or end_date is NULL) ';
        }
        $sql = "SELECT * FROM " . ZM_TABLE_GROUP_PRICING . "
                WHERE products_id = :productId
                AND group_id = :groupId".$dateLimit;
        $args = array('productId' => $productId, 'groupId' => $groupId);
        return ZMRuntime::getDatabase()->querySingle($sql, $args, ZM_TABLE_GROUP_PRICING, 'ProductGroupPricing');
    }


    /**
     * Create a new group pricing.
     *
     * @param ProductGroupPricing groupPricing The new product group pricing.
     * @return ProductGroupPricing The created product group pricing incl. the id.
     */
    function createProductGroupPricing($groupPricing) {
        return ZMRuntime::getDatabase()->createModel(ZM_TABLE_GROUP_PRICING, $groupPricing);
    }

    /**
     * Update an existing product group pricing.
     *
     * @param ProductGroupPricing groupPricing The new product group pricing.
     * @return ProductGroupPricing The updated product group pricing.
     */
    function updateProductGroupPricing($groupPricing) {
        ZMRuntime::getDatabase()->updateModel(ZM_TABLE_GROUP_PRICING, $groupPricing);
        return $groupPricing;
    }

}

?>
