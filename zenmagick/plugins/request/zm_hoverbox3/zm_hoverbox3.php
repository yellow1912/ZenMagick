<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Plugin to enable support for Hover Box3 in ZenMagick.
 *
 * @package org.zenmagick.plugins.zm_hoverbox3
 * @author mano
 * @version $Id$
 */
class zm_hoverbox3 extends Plugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Hover Box3', 'Hover Box3 support for ZenMagick', '${plugin.version}');
        $this->setLoaderPolicy(ZMPlugin::LP_ALL);
        $this->setScope(Plugin::SCOPE_STORE);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * {@inheritDoc}
     */
    public function install() {
        parent::install();
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDirectory()."sql/use-sql-patch-tool-to-install.txt")), $this->messages_);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($keepSettings=false) {
        parent::remove($keepSettings);
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDirectory()."sql/uninstall-HoverBox-sql.txt")), $this->messages_);
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();
        ZMEvents::instance()->attach($this);
    }

    /**
     * {@inheritDoc}
     */
    public function onZMFinaliseContents($args) {
        $contents = $args['contents'];
        if (false === strpos($contents, 'hoverbox')) {
            return null;
        }

        $theme = Runtime::getTheme();
        // hover3 used in this page
        $h3head = '';
        $h3head .= '<link rel="stylesheet" type="text/css" href="' . $theme->themeURL('hover3/stylesheet_hoverbox3.css', false) . '" />';
        $h3head .= '<script type="text/javascript" src="' . $theme->themeURL('hover3/ic_effects.js', false) . '"></script>';
        // eval js config
        $h3config_tpl = file_get_contents($this->getPluginDirectory().'/ic_hoverbox_config.tpl');
        ob_start();
        eval('?>'.$h3config_tpl);
        $h3config = ob_get_clean();
        $h3head .= $h3config;
        $h3head .= '<script type="text/javascript" src="' . $theme->themeURL('hover3/ic_hoverbox3.js', false) . '"></script>';
        $contents = preg_replace('/<\/head>/', $h3head.'</head>', $contents, 1);

        $args['contents'] = $contents;
        return $args;
    }

}

?>
