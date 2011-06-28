<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
namespace zenmagick\apps\store;

use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;

/**
 * Shared store event listener.
 *
 * <p>This is the ZenMagick store bootstrapper.</p>
 *
 * @author DerManoMann
 * @package zenmagick.apps.store
 */
class StoreEventListener {

    /**
     * Keep up support for local.php.
     */
    public function onBootstrapDone($event) {
        // in old zencart
        Runtime::getSettings()->set('zenmagick.core.plugins.context', (defined('IS_ADMIN_FLAG') && IS_ADMIN_FLAG) ? 2 : 1);

        // set default
        Runtime::getSettings()->set('zenmagick.base.plugins.dirs', array(
            Runtime::getInstallationPath().'plugins'.DIRECTORY_SEPARATOR,
            Runtime::getInstallationPath().'apps/admin/plugins'.DIRECTORY_SEPARATOR,
            Runtime::getInstallationPath().'apps/store/plugins'.DIRECTORY_SEPARATOR
        ));

        // random defines that we might need
        if (!defined('PRODUCTS_OPTIONS_TYPE_SELECT')) {
            define('PRODUCTS_OPTIONS_TYPE_SELECT', 0);
        }
        if (!defined('ATTRIBUTES_PRICE_FACTOR_FROM_SPECIAL')) {
            define('ATTRIBUTES_PRICE_FACTOR_FROM_SPECIAL', 0);
        }

        // include some zencart files we need.
        include_once ZC_INSTALL_PATH . 'includes/database_tables.php';

        //** load all config values if not set **//
        if (!defined('GLOBAL_SET_TIME_LIMIT')) {
            foreach (\ZMConfig::instance()->loadAll() as $key => $value) {
                define($key, $value);
            }
        }

        // load some static files that we still need
        $statics = array(
          'shared/defaults.php',
          'lib/core/external/zm-pomo-3.0.packed.php',
          'lib/core/services/locale/_zm.php',
          'shared/external/lastRSS.php',
          // admin
          'apps/'.ZM_APP_NAME.'/lib/local.php',
          'apps/'.ZM_APP_NAME.'/lib/menu.php',
          'apps/'.ZM_APP_NAME.'/lib/utils/sqlpatch.php',
          // store
          'apps/'.ZM_APP_NAME.'/lib/email.php',
          'apps/'.ZM_APP_NAME.'/lib/zencart_overrides.php',
        );
        foreach ($statics as $static) {
            $file = Runtime::getInstallationPath().$static;
            if (file_exists($file)) {
                require_once $file;
            }
        }

        // set shared defaults again as some settings depend on zencart settings...
        \ZMSettings::addAll(zm_get_default_settings());

        $local = Runtime::getInstallationPath().DIRECTORY_SEPARATOR.'local.php';
        if (file_exists($local)) {
            include $local;
        }
    }

}
