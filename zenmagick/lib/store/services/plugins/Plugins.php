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
 * Plugins.
 *
 * <p>The plugin type is controlled by the base directory within the plugins directory.
 * Please note that even though it is valid to create payment, shipping and order_total
 * directories/plugins, zen-cart will not (yet) recognize them as such.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.service
 * @version $Id: ZMPlugins.php 2360 2009-06-30 03:31:15Z dermanomann $
 */
class Plugins extends ZMPlugins {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
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
        return ZMObject::singleton('Plugins');
    }


    /**
     * {@inheritDoc}
     */
    protected function loadStatus() {
        $status = unserialize(ZENMAGICK_PLUGIN_STATUS);
        if (!is_array($status)) {
            $status = array();
        }
        foreach ($status as $id => $details) {
            // XXX: comp. hack, remove
            if (!isset($status[$id]['group'])) {
                $status[$id]['group'] = $details['type'];
            }
        }
        return $status;
    }

    /**
     * {@inheritDoc}
     */
    protected function comparePlugins($a, $b) {
        $ao = $a->getSortOrder();
        $bo = $b->getSortOrder();
        if ($ao == $bo) {
            return parent::comparePlugins($a, $b);
        }
        return ($ao < $bo) ? -1 : 1;
    }

    /**
     * Init all plugins of the given groups and scope.
     *
     * @param mixed groups The group or list of groups.
     * @param string scope The scope.
     */
    public function initPluginsForGroupsAndScope($groups, $scope) {
        if (!is_array($groups)) {
            $groups = array($groups);
        }

        $ids = array();
        foreach ($groups as $group) {
            foreach ($this->getPluginsForGroup($group, true) as $plugin) {
                if (Plugin::SCOPE_ALL == $plugin->getScope() || $plugin->getScope() == $scope) {
                    $ids[] = $plugin->getId();
                }
            }
        }

        return $this->initPluginsForId($ids, true);
    }

}

?>
