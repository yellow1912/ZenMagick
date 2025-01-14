<?php
/*
 * ZenMagick - Another PHP framework.
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
use zenmagick\base\Runtime;

// main request processor
$container = Runtime::getContainer();
if (!array_key_exists('zencart', $container->get('themeService')->getActiveTheme()->getMeta())) {
    // pick up session data changed by zencart code
    $_zm_session = $_zm_request->getSession();
    foreach ($_SESSION as $key => $value) {
        $_zm_session->setValue($key, $value);
    }
    $container->get('dispatcher')->dispatch($_zm_request);
    exit;
} else {
    // do ob_start() to allow plugins to do their magic with zen cart templates too
    ob_start();
}
