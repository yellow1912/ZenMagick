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
 *
 * $Id$
 */
?>
<?php

    // mark CLI calls
    define('ZM_CLI_CALL', defined('STDIN'));
    // start time for stats
    define('ZM_START_TIME', microtime());

    define('ZM_BASE_DIR', dirname(__FILE__).DIRECTORY_SEPARATOR);

    error_reporting(E_ALL^E_NOTICE);
    // hide as to avoid filenames that contain account names, etc.
    @ini_set("display_errors", false);
    @ini_set("log_errors", true); 
    @ini_set("register_globals", false);

    // ZenMagick bootstrap
    if (!IS_ADMIN_FLAG && file_exists(ZM_BASE_DIR.'core.php')) {
        require ZM_BASE_DIR.'core.php';
    } else {
        require_once ZM_BASE_DIR."core/settings/constants.php";
        require_once ZM_BASE_DIR."core/ZMSettings.php";
        require_once ZM_BASE_DIR."core/settings/defaults.php";
        require_once ZM_BASE_DIR."core/ZMLoader.php";

        // prepare loader
        ZMLoader::instance()->addPath(ZM_BASE_DIR.'core'.DIRECTORY_SEPARATOR);
        ZMLoader::instance()->loadStatic();
    }

    // as default disable plugins for CLI calls
    ZMSettings::set('isEnablePlugins', !ZM_CLI_CALL);

    // load global settings
    if (file_exists(ZM_BASE_DIR.'local.php')) {
        require_once ZM_BASE_DIR.'local.php';
    }

    // set the default authentication provider
    ZMAuthenticationManager::instance()->addProvider(ZMSettings::get('defaultAuthenticationProvider'), true);

    if (ZMSettings::get('isEnablePlugins')) {
        // upset plugins
        ZMLoader::make("Plugins");
        ZMPlugins::initPlugins(explode(',', ZMSettings::get('plugins.types')), ZMRuntime::getScope());
    }

    // register custom error handler
    if (ZMSettings::get('isZMErrorHandler') && null != ZMSettings::get('zmLogFilename')) {
        set_error_handler(array(ZMLogging::instance(), 'errorHandler'));
        set_exception_handler(array(ZMLogging::instance(), 'exceptionHandler'));
    }

    // core and plugins loaded
    ZMEvents::instance()->fireEvent(null, ZMEvents::BOOTSTRAP_DONE);

    if (ZMSettings::get('isEnableZMThemes') || ZM_CLI_CALL) {
        // resolve theme to be used 
        $_zm_theme = ZMThemes::instance()->resolveTheme(ZMSettings::get('isEnableThemeDefaults') ? ZM_DEFAULT_THEME : ZMRuntime::getThemeId());
        ZMRuntime::setTheme($_zm_theme);

        // load default mappings
        zm_set_default_url_mappings();
        zm_set_default_sacs_mappings();

        if (!ZMSettings::get('isAdmin')) {
            // make sure to use SSL if required
            ZMSacsMapper::instance()->ensureAccessMethod(ZMRequest::getPageName());
        }

        // now we can check for a static homepage
        if (!ZMTools::isEmpty(ZMSettings::get('staticHome')) && 'index' == ZMRequest::getPageName() 
            && (0 == ZMRequest::getCategoryId() && 0 == ZMRequest::getManufacturerId())) {
            require ZMSettings::get('staticHome');
            exit;
        }
    }

    // start output buffering
    // XXX: handle admin?
    if (!ZMSettings::get('isAdmin')) { ob_start(); }

    require_once(ZM_BASE_DIR.'zc_fixes.php');

    // always echo in admin
    if (ZMSettings::get('isAdmin')) { ZMSettings::get('isEchoHTML', true); }
    // this is used as default value for the $echo parameter for HTML functions
    define('ZM_ECHO_DEFAULT', ZMSettings::get('isEchoHTML'));

    // load stuff that really needs to be global!
    foreach (ZMLoader::instance()->getGlobal() as $_zm_global) {
        include_once $_zm_global;
    }

    // XXX: move to ZMDbTableMapper
    // handle db table mapping caching
    $tableMapper = ZMDbTableMapper::instance();
    if (ZMSettings::get('isCacheDbMappings') && !$tableMapper->isCached()) {
        $tableMapper->updateCache();
    }

    // pick up messages from zen-cart request handling
    ZMMessages::instance()->_loadMessageStack();

    ZMEvents::instance()->fireEvent(null, ZMEvents::INIT_DONE);

?>
