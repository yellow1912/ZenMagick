<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

use zenmagick\base\Beans;
use zenmagick\base\Toolbox;
use zenmagick\apps\store\controller\CatalogContentController;

/**
 * Admin controller.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.plugins.productGroupPricing
 */
class ZMProductGroupPricingTabController extends CatalogContentController {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('product_group_pricing_tab', _zm('Group Pricing'), self::ACTIVE_PRODUCT);
    }


    /**
     * {@inheritDoc}
     */
    public function getViewData($request) {
        $priceGroups = $this->container->get('groupPricingService')->getPriceGroups();
        $productGroupPricingService = $this->container->get('productGroupPricingService');
        $groupId = $request->getParameter('groupId', $priceGroups[0]->getId());
        $productGroupPricings = $productGroupPricingService->getProductGroupPricings($request->getProductId(), $groupId, false);
        $productGroupPricing = Beans::getBean("ZMProductGroupPricing");
        // TODO: should not need to check for delete - viewData should not override findView(.., data) data
        if (null != ($groupPricingId = $request->getParameter('groupPricingId')) && 0 < $groupPricingId && null == $request->getParameter('delete')) {
            $productGroupPricing = $productGroupPricingService->getProductGroupPricingForId($groupPricingId);
        }
        return array(
            'groupId' => $groupId,
            'priceGroups' => $priceGroups,
            'productGroupPricing' => $productGroupPricing,
            'productGroupPricings' => $productGroupPricings
        );
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        //TODO: this should be POST!!
        if (Toolbox::asBoolean($request->getParameter('delete'))) {
            $productGroupPricing = Beans::getBean("ZMProductGroupPricing");
            $productGroupPricing->populate($request);
            // delete
            $this->container->get('productGroupPricingService')->updateProductGroupPricing($productGroupPricing);
        }
        return $this->findView(null, array('productGroupPricing' => Beans::getBean("ZMProductGroupPricing")));
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $productGroupPricing = Beans::getBean("ZMProductGroupPricing");
        $productGroupPricingService = $this->container->get('productGroupPricingService');
        $productGroupPricing->populate($request);
        if (0 == $productGroupPricing->getId()) {
            // create
            $productGroupPricing = $productGroupPricingService->createProductGroupPricing($productGroupPricing);
        } else {
            // update
            $productGroupPricing = $productGroupPricingService->updateProductGroupPricing($productGroupPricing);
        }

        return $this->findView('catalog-redirect');
    }

}
