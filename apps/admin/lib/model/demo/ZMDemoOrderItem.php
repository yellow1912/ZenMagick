<?php
/*
 * ZenMagick - Smart e-commerce
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
 * Demo order item.
 *
 * @author DerManoMann
 * @package zenmagick.store.admin.model.demo
 */
class ZMDemoOrderItem extends ZMOrderItem {
    private $index_;


    /**
     * Create new instance.
     *
     * @param int index Optional index.
     */
    function __construct($index=1) {
        parent::__construct();
        $this->index_ = $index;
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
    public function getQty() {
        return $this->index_*2;
    }

    /**
     * {@inheritDoc}
     */
    public function getName() {
        return 'Order Item #'.$this->index_;
    }

    /**
     * {@inheritDoc}
     */
    public function getCalculatedPrice() {
        return $this->index_*19.99;
    }

}
