<?php
/*
 * ZenMagick - Another PHP framework.
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
 * <p>A editor select form widget.</p>
 *
 * <p>This widget will append a list of all available editors to the options list. That
 * means the generic <em>options</em> propert may be used to set custom options that will show
 * up at the top of the list.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.widgets
 * @version $Id$
 */
class ZMEditorSelectFormWidget extends ZMSelectFormWidget {

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
     * {@inheritDoc}
     */
    public function getOptions($request) {
        return array_merge(parent::getOptions($request), $request->getToolbox()->utils->getEditorMap());
    }

}
