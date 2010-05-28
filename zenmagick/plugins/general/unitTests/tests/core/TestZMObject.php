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
 * Test ZMObject.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 * @version $Id$
 */
class TestZMObject extends ZMTestCase {

    /**
     * Test property names.
     */
    public function testPropertyNames() {
        $obj = new ZMObject();
        $obj->set('foo', 'bar');
        $obj->set('deng', 'poh');
        // custom only
        $this->assertEqual(array('foo', 'deng'), $obj->getPropertyNames(true));
        // all
        $this->assertEqual(array('foo', 'deng', 'propertyNames', 'properties', 'attachedMethods'), $obj->getPropertyNames(false));
    }

    /**
     * Test properties.
     */
    public function testProperties() {
        $obj = new ZMObject();
        $obj->set('foo', 'bar');
        $obj->set('deng', 'poh');
        // custom only
        $this->assertEqual(array('foo' => 'bar', 'deng' => 'poh'), $obj->getProperties());
    }


}