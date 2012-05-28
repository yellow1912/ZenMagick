<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
 * ZenCart admin controller
 *
 * @author Johnny Robeson
 * @todo <johnny> we could try to untangle GET/POST mess, but is it really worth it?
 */
class AdminController extends \ZMController {

    public function getViewData($request) {
        /**
         * Boot the ZenCart admin
         */
        $this->container->get('productTypeLayoutService')->defineAll();

        $autoLoader = $this->container->get('zencartAutoLoader');
        $autoLoader->setErrorLevel();

        $autoLoader->includeFiles('../includes/configure.php');
        $autoLoader->includeFiles('../includes/version.php'); // used by the paypal modules!
        $autoLoader->includeFiles('includes/extra_configures/*.php');
        $autoLoader->includeFiles('../includes/filenames.php');
        $autoLoader->includeFiles('includes/extra_datafiles/*.php');
        $autoLoader->includeFiles('includes/functions/extra_functions/*.php');
        $autoLoader->includeFiles('includes/functions/{general.php,database.php,functions_customers.php,functions_metatags.php,functions_prices.php,html_output.php,localization.php,password_funcs.php}');
        $autoLoader->includeFiles('../includes/functions/{audience.php,banner.php,featured.php,functions_email.php,salemaker.php,sessions.php,specials.php,zen_mail.php}');


        $themeService = $this->container->get('themeService');
        $themeService->initThemes($request->getSelectedLanguage());
        $themeId = $themeService->getActiveThemeId();
        define('DIR_WS_TEMPLATE', DIR_WS_TEMPLATES.$themeId.'/');
        define('DIR_WS_TEMPLATE_IMAGES', DIR_WS_TEMPLATE.'images/');
        define('DIR_WS_TEMPLATE_ICONS', DIR_WS_TEMPLATE_IMAGES.'icons/');
        $autoLoader->setGlobalValue('template_dir', $themeId);
        $autoLoader->setGlobalValue('PHP_SELF', $request->getRequestId());

        $autoLoader->restoreErrorLevel();

        $autoLoader->setGlobalValue('zc_products', new \products);

        $tpl = array('autoLoader' => $autoLoader);
        foreach($autoLoader->getGlobalValues() as $k => $v) {
            $tpl[$k] = $v;
        }
        return $tpl;
    }


    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        // @todo remove once we rely on ZenCart 1.5.0
        if ($request->getMethod() == 'GET') { // from init_general_funcs
            foreach ($request->getParameterMap() as $k =>$v) {
                $request->setParameter($k, strip_tags($v));
            }
        }


        $session = $request->getSession();
        $language = $request->getSelectedLanguage();
        $session->setValue('language', $language->getDirectory());
        $session->setValue('languages_id', $language->getId());
        $session->setValue('languages_code', $language->getCode());


        if (null == $session->getValue('securityToken')) {
            $session->setValue('securityToken', $session->getToken());
        }

        // strangely whos_online is the only user. @todo test ZM version of whos_online
        $session->setValue('currency', Runtime::getSettings()->get('defaultCurrency'));

        if (null == $session->getValue('selected_box')) {
            $session->setValue('selected_box', 'configuration');
        }

        $selectedBox = $request->getParameter('selected_box');
        if (null != $selectedBox) {
            $session->setValue('selected_box', $selectedBox);
        }

        // @todo add option to store data in $_SESSION for zc admin too so the values can be used bidirectionally
        $_SESSION = $session->getData();

        $tpl = array();
        $view = $this->findView('zc_admin', $tpl);
        // no layout for invoice/packaging slip
        if (in_array($request->getRequestId(), Runtime::getSettings()->get('apps.store.zencart.skipLayout', array()))) {
            $view->setLayout(null);
        }
        return $view;
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        if (!$this->validateSecurityToken($request)) {
            $this->messageService->error(_zm('Security token validation failed'));
            $request->redirect($_SERVER['HTTP_REFERER']);
        }
        return $this->processGet($request);
    }

    /**
     * Implementation of ZenCart's init_session securityToken checking code
     *
     * Most of this code is only useful for 1.3.9 and not 1.5.0
     *
     * @todo require 1.5.0? we could drop all of thise code if we implemented the above
     * @todo should we dynamically add to tokenSecuredForms instead and let ZMRequest handle it?
     */
    public function validateSecurityToken($request) {
        $action = $request->getParameter('action', '');
        $valid = true; // yuck. need 1.5.0 or all these options implemented ourselves
        if (in_array($action, array('copy_options_values', 'update_options_values', 'update_value', 'add_product_option_values', 'copy_options_values_one_to_another_options_id', 'delete_options_values_of_option_name', 'copy_options_values_one_to_another', 'copy_categories_products_to_another_category_linked', 'remove_categories_products_to_another_category_linked', 'reset_categories_products_to_another_category_master', 'update_counter', 'update_orders_id', 'locate_configuration_key', 'locate_configuration', 'update_categories_attributes', 'update_product', 'locate_configuration', 'locate_function', 'locate_class', 'locate_template', 'locate_all_files', 'add_product', 'add_category', 'update_product_attribute', 'add_product_attributes', 'update_attributes_copy_to_category', 'update_attributes_copy_to_product', 'delete_option_name_values','delete_all_attributes', 'save', 'layout_save', 'update', 'update_sort_order', 'update_confirm', 'copyconfirm', 'deleteconfirm', 'insert', 'move_category_confirm', 'delete_category_confirm', 'update_category_meta_tags', 'insert_category' ))) {
            if (!in_array($request->getRequestId(), array('products_price_manager', 'option_name', 'currencies', 'languages', 'specials', 'featured', 'salemaker'))) {
                $valid = $request->getSession()->getToken() == $request->getParameter('securityToken');
            }
        }
        return $valid;
    }
}
