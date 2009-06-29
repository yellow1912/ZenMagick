<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * Plugin adding group based pricing.
 *
 * @package org.zenmagick.plugins.zm_group_pricing
 * @author DerManoMann
 * @version $Id$
 */
class zm_group_pricing extends Plugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Group Pricing', 'Group Pricing', '${zenmagick.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_FOLDER);
        $this->setPreferredSortOrder(15);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Init this plugin.
     */
    function init() {
        parent::init();

        ZMLoader::resolve("ProductGroupPricingService");
        if (0 < ZMRequest::getProductId()) {
            // only available if product involved
            $this->addMenuItem('zm_group_pricing_admin', zm_l10n_get('Group Pricing'), 'zm_group_pricing_admin', ZMAdminMenu::MENU_CATALOG_MANAGER_TAB);
        }
    }

    /**
     * Install this plugin.
     */
    function install() {
        parent::install();
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDir()."sql/group_pricing.sql")), $this->messages_);
    }

    /**
     * Remove this plugin.
     *
     * @param boolean keepSettings If set to <code>true</code>, the settings will not be removed; default is <code>false</code>.
     */
    function remove($keepSettings=false) {
        parent::remove($keepSettings);
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDir()."sql/uninstall.sql")), $this->messages_);
    }

}

?>
