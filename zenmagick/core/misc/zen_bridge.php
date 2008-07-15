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
 *
 * $Id$
 */
?>
<?php

if (!function_exists('zen_href_link')) {

    /**
     * zen_href_link wrapper that delegates to the Zenmagick implementation.
     */
    function zen_href_link($page='', $params='', $transport='NONSSL', $addSessionId=true, $seo=true, $isStatic=false, $useContext=true) {
        if (function_exists('_zm_build_href')) {
            return _zm_build_href($page, $params, $transport == 'SSL', false);
        } else if (function_exists('zen_href_link_DISABLED')) {
            // just in case...
            return zen_href_link_DISABLED($page, $params, $transport, $addSessionId, $seo, $isStatic, $useContext);
        } else {
            ZMObject::backtrace("can't find zen_href_link implementation");
        }
    }

}


    function zm_get_attributes_price_final($arg1, $args2, $arg3, $arg4) {
        return zen_get_attributes_price_final($arg1, $args2, $arg3, $arg4);
    }
    function zm_get_attributes_price_final_onetime($arg1, $args2, $arg3) {
        return zen_get_attributes_price_final_onetime($arg1, $args2, $arg3);
    }
    function zm_get_discount_calc($arg1, $args2, $arg3) { return zen_get_discount_calc($arg1, $args2, $arg3); }

?>
