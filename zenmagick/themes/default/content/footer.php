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

<div id="footer">
    <p id="fpages">
        <?php foreach (ZMEZPages::instance()->getPagesForFooter() as $page) { ?>
            <?php $html->ezpageLink($page->getId()) ?>
        <?php } ?>
    </p>

    <?php if (null != ($bannerBox = ZMBanners::instance()->getBannerForSet('footer2'))) { ?>
        <div id="bannerFive"><?php $macro->showBanner($bannerBox); ?></div>
    <?php } ?>

    <p>Powered by <a href="http://www.zen-cart.com">zen-cart</a> and <a href="http://www.zenmagick.org">ZenMagick</a></p>
    <p>&copy; 2006-2008  <a href="http://www.zenmagick.org">ZenMagick</a> | Design based on andreas08 by <a href="http://andreasviklund.com">Andreas Viklund</a></p>
</div>
