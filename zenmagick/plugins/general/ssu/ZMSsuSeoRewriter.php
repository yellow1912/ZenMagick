<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 * SSU rewriter.
 *
 * @package org.zenmagick.plugins.ssu
 * @author mano
 * @version $Id$
 */
class ZMSsuSeoRewriter implements ZMSeoRewriter {

    /**
     * {@inheritDoc}
     */
    public function rewrite($request, $args) {
        $requestId = $args['requestId'];
        $params = $args['params'];
        $secure = $args['secure'];
        $addSessionId = $args['addSessionId'];
        $isStatic = $args['isStatic'];
        $useContext = $args['useContext'];

        if ($requestId == 'category') { $requestId = 'index'; }
        global $ssu;
        if (isset($ssu) && ($link = $ssu->ssu_link($requestId, $params, $secure ? 'SSL' : 'NONSSL', $addSessionId, false, $isStatic, $useContext)) != false) {
            return $link;
        }

        return null;
    }

}

?>
