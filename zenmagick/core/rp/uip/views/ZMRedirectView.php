<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 * Redirect view.
 *
 * @author mano
 * @package net.radebatz.zenmagick.rp.uip.views
 * @version $Id$
 */
class ZMRedirectView extends ZMView {
    var $url_;


    /**
     * Create a new redirect view.
     *
     * @param string url The redirect url.
     * @param bool secure Flag whether to redirect using a secure URL or not.
     */
    function ZMRedirectView($view, $secure=false) {
        if ($secure) {
            $this->url_ = zm_secure_href($view, '', false);
        } else {
            $this->url_ = zm_href($view, '', false);
        }
    }

    /**
     * Create a new redirect view.
     *
     * @param string url The redirect url.
     * @param bool secure Flag whether to redirect using a secure URL or not.
     */
    function __construct($view, $secure=false) {
        $this->ZMRedirectView($view, $secure);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Generate view response.
     */
    function generate() { 
        zm_redirect($this->url_);
        zm_exit();
    }

}

?>
