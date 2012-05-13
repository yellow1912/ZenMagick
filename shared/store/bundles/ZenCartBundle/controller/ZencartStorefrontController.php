<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

namespace zenmagick\apps\store\bundles\ZenCartBundle\controller;

use zenmagick\base\Runtime;

/**
 * ZenCart storefront controller
 *
 * @author Johnny Robeson
 * @package org.zenmagick.plugins.zenCart
 */
class ZencartStorefrontController extends \ZMController {

    private $controllerFile;
    private $useZenCartTemplate = false;

    /**
     * Override getFormData() for ZenCart pages
     */
    public function getFormData($request, $formDef=null, $formId=null) {
        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function preProcess($request) {
        /**
         * This code is taken directly from application_top.php.
         * @copyright Copyright 2003-2010 Zen Cart Development Team
         */
        $paramsToCheck = array('main_page', 'cPath', 'products_id', 'language', 'currency', 'action', 'manufacturers_id',
            'pID', 'pid', 'reviews_id', 'filter_id', 'zenid', 'sort', 'number_of_uploads', 'notify', 'page_holder', 'chapter',
            'alpha_filter_id', 'typefilter', 'disp_order', 'id', 'key', 'music_genre_id', 'record_company_id', 'set_session_login',
            'faq_item', 'edit', 'delete', 'search_in_description', 'dfrom', 'pfrom', 'dto', 'pto', 'inc_subcat', 'payment_error',
            'order', 'gv_no', 'pos', 'addr', 'error', 'count', 'error_message', 'info_message', 'cID', 'page', 'credit_class_error_code');
        foreach($paramsToCheck as $key) {
            if (isset($_GET[$key]) && !is_array($_GET[$key])) {
                if (substr($_GET[$key], 0, 4) == 'http' || strstr($_GET[$key], '//') || strlen($_GET[$key]) > 43) {
                    header('HTTP/1.1 406 Not Acceptable');
                    exit(0);
                }
            }
        }
        $this->container->get('productTypeLayoutService')->defineAll();
        $autoLoader = $this->container->get('zencartAutoLoader');

        if ('ipn_handler' == $request->getRequestId()) { // @todo handle other common zencart entry points like googlebase
            $this->controllerFile = $autoLoader->resolveFile('ipn_main_handler.php');
            return;
        }

        /**
         * Does the page controller exist?
         */
        $themeMeta = $this->container->get('themeService')->getActiveTheme()->getConfig('meta');
        $this->useZenCartTemplate = isset($themeMeta['zencart']);
        $controllerFile = $autoLoader->resolveFile('includes/modules/pages/%current_page_base%/header_php.php');
        if (!file_exists($controllerFile)) {
            if (MISSING_PAGE_CHECK == 'On' || MISSING_PAGE_CHECK == 'true') {
                $request->setRequestId('index');
            } elseif (MISSING_PAGE_CHECK == 'Page Not Found') {
                header('HTTP/1.1 404 Not Found');
                $request->setRequestId('page_not_found');
            }
            $controllerFile = $autoLoader->resolveFile('includes/modules/pages/%current_page_base%/header_php.php');
        }
        $this->controllerFile = $controllerFile;

    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $settingsService = $this->container->get('settingsService');
        $useZenCartTemplate = $this->useZenCartTemplate;
        $session = $request->getSession();
        if ('GET' == $request->getMethod() && $useZenCartTemplate) {
            /**
             * validate products_id for search engines and bookmarks, etc.
             */
            $productId = $request->getProductsId();
            $session = $request->getSession();
            if (!empty($productId) && $session->getValue('check_valid')) {
                $validProduct = $this->container->get('productService')->getProductForId($productId);
                if (null == $validProduct) {
                    $session->setValue('check_valid', false);
                    zen_redirect(zen_href_link(zen_get_info_page($productId), 'products_id='.$productId));
                }
            } else {
                $session->setValue('check_valid' , true);
            }
        }

        $autoLoader = $this->container->get('zencartAutoLoader');
        /**
         *  Get globals used throughout.
         */
        extract($autoLoader->getGlobalValues());
        /**
         *  Get "local" globals.
         *
         *  Almost entirely related to shipping, payment, order total and checkout.
         */
        global $shipping, $shipping_num_boxes, $shipping_weight, $shipping_quoted, $shipping_modules;
        global $order_totals, $order_total_modules, $payment_modules;
        global $order, $total_count, $total_weight;
        global $credit_covers, $country_info, $discount_coupon, $insert_id;
        global $isECtransaction, $isDPtransaction;
        $zcShipping = $session->getValue('shipping', null);
        if (null != $zcShipping) {
            if (is_array($zcShipping)) $zcShipping = $zcShipping['id'];
            list($module, $method) = explode('_', $zcShipping);
            global $$module;
        }
        if (null != ($sPayment = $session->getValue('payment', null))) {
            global $$sPayment;
        }

        /**
         *  Execute ZenCart controller
         */
        $cwd = getcwd();
        $autoLoader->setErrorLevel();
        chdir($settingsService->get('apps.store.zencart.path'));
        extract($this->getZcViewData($request));
        require($this->controllerFile);

        if ($useZenCartTemplate) {
            require($template->get_template_dir('html_header.php',DIR_WS_TEMPLATE, $current_page_base,'common'). '/html_header.php');
            require($template->get_template_dir('main_template_vars.php',DIR_WS_TEMPLATE, $current_page_base,'common'). '/main_template_vars.php');
            require($template->get_template_dir('tpl_main_page.php',DIR_WS_TEMPLATE, $current_page_base,'common'). '/tpl_main_page.php');
            echo '</html>';
        }
        chdir($cwd);
        $autoLoader->restoreErrorLevel();
        return null;
    }


    /**
     * Get ZenCart view data.
     */
    public function getZcViewData($request) {
        // category path - no support for get_terms_to_filter table. does anybody use that?
        $manufacturerId = $request->getManufacturersId();
        $show_welcome = false;
        if (null == $request->getCategoryPath()) {
            if (!empty($productId) && empty($manufacturerId)) {
                $request->setParameter('cPath', zen_get_product_path($productId));
            } else if (SHOW_CATEGORIES_ALWAYS == '1' && empty($manufacturerId)) {
                $show_welcome = true;
                $request->setParameter('cPath', (defined('CATEGORIES_START_MAIN') ? CATEGORIES_START_MAIN : ''));
            }
        }
        // end category path

        define('PAGE_PARSE_START_TIME', microtime());

        $lng = new \language();

        $languageId = $request->getSession()->getLanguageId();
        // breadcrumb
        $robotsNoIndex = false;
        $validCategories = array();
        $cPathArray = $request->getCategoryPathArray();
        foreach ($cPathArray as $categoryId) {
            $category = $this->container->get('categoryService')->getCategoryForId($categoryId, $languageId);
            if (null != $category) {
                $validCategories[] = $category;
            } else if (SHOW_CATEGORIES_ALWAYS == 0) {
                $robotsNoIndex = true;
                break;
            }
        }
        // ZenMagick does most of the following , we should be able to reuse it.
        $manufacturer = null;
        $manufacturerId = $request->getManufacturerId();
        if (null != $manufacturerId) {
            $manufacturer = $this->container->get('manufacturerService')->getManufacturerForId($manufacturerId, $languageId);
        }
        $product = null;
        if (null != $request->getProductId()) {
            $product = $this->container->get('productService')->getProductForId($request->getProductId());
        }
        $breadcrumb = $this->initCrumbs($validCategories, $manufacturer, $product);
        // end breakdcrub

        $canonicalLink = $this->getCanonicalUrl();
        $this_is_home_page = $this->isHomePage();
        $zv_onload = $this->getOnLoadJS();
        return compact('breadcrumb', 'canonicalLink', 'lng', 'robotsNoIndex', 'this_is_home_page', 'zv_onload');
    }

    /**
     * Figures out if the current page is a product listing ($this_is_home_page)
     *
     * @return string
     */
    public function isHomePage() {
        $request = $this->container->get('request');
        return 'index' == $request->getRequestId() && null == $request->getCategoryPath()
            && null == $request->getManufacturerId() && '' == $request->getParameter('type_filter', '');
    }

    /**
     * Get a canonical link to a page.
     *
     * It's mostly the same as init_canonical except with almost
     * all the exceptions removed.
     * If people actually edit that file then we should handle it
     * completely different, but it is unlikely that many
     * people actually edit the file.
     *
     * CHANGES:
     *   All page specific switches have been removed as they were
     *   just placeholders for future editors (as noted above).
     *
     *   Exclusion list has been shortened by parameters already fixed
     *   by $request->url()
     *
     */
    private function getCanonicalUrl() {
        $request = $this->container->get('request');
        $requestId = $request->getRequestId();
        // EXCLUDE certain parameters which should not be included in canonical links:
        // @todo blacklist bad! whitelist good!
        $exclusionList = array('action', 'currency', 'typefilter', 'gclid', 'search_in_description',
            'pto', 'pfrom', 'dto', 'dfrom', 'inc_subcat', 'disp_order', 'page', 'sort', 'alpha_filter_id',
             'filter_id', 'utm_source', 'utm_medium', 'utm_content', 'utm_campaign', 'language'
        );

        if ($this->isHomePage()) {
            $url = $request->getBaseUrl();
        } else if (\ZMLangUtils::endsWith($requestId, 'info') && null != ($productId = $request->getProductId())) {
            $url = $request->getToolbox()->net->product($productId, null);
        } else {
            $url = $request->url($requestId, rtrim(zen_get_all_get_params($exclusionList), '&'));
        }
        return $url;
    }

    /**
     * Get javascript code from on_load.js files in ZC pages/templates.
     *
     * Returns "onLoad" inline js code used by
     * ZenCart templates.
     *
     * @return string javascript code.
     */
    public function getOnLoadJS() {
        $autoLoader = $this->container->get('zencartAutoLoader');
        $js = '';
        $pageOnLoad = $autoLoader->resolveFiles('includes/modules/pages/%current_page_base%/on_load_*.js');
        $templateOnLoad = $autoLoader->resolveFiles('includes/templates/%template_dir%/jscript/on_load/on_load_*.js');
        $files = array_merge($pageOnLoad, $templateOnLoad);
        foreach ($files as $file) {
            $js .= rtrim(file_get_contents($file), ';').';';
        }
        return $js;
    }

    /**
     * Initialize the breadcrumb for template usage.
     */
    public function initCrumbs($categories = null, $manufacturer = null, $product = null) {
        $breadcrumb = new \breadcrumb();

        $breadcrumb->add(HEADER_TITLE_CATALOG, zen_href_link(FILENAME_DEFAULT));
        $request = $this->container->get('request');
        $languageId = $request->getSession()->getLanguageId();

        foreach ((array)$categories as $category) {
                $breadcrumb->add($category->getName(), zen_href_link(FILENAME_DEFAULT, implode('_', $category->getPath())));
        }

        if (null != $manufacturer) {
            $breadcrumb->add($manufacturer->getName(), zen_href_link(FILENAME_DEFAULT, 'manufacturers_id='.$manufacturer->getId()));
        }

        // Add Product
        if (null != $product) {
            $breadcrumb->add($product->getName(), zen_href_link(zen_get_info_page($product->getId()), 'cPath='.$request->getCategoryPath().'&products_id='.$product->getId()));
        }

        return $breadcrumb;
    }


}
