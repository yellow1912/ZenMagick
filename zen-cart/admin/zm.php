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
 *
 * $Id$
 */
?><?php

  // make zc happy
  define('IS_ADMIN_FLAG', true);

  // app location relative to zenmagick installation (ZM_BASE_PATH)
  define('ZM_APP_PATH', 'apps'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR);

  // share code
  define('ZM_SHARED', 'shared');

  // preload a couple zc files needed
  require_once 'includes/configure.php';
  require_once DIR_FS_CATALOG.DIR_WS_INCLUDES.'filenames.php';
  require_once DIR_FS_CATALOG.DIR_WS_INCLUDES.'database_tables.php';

  require_once '../zenmagick/bootstrap.php';
  ZMSettings::set('zenmagick.mvc.request.index', 'zm.php');
  require_once '../zenmagick/mvc.php';
