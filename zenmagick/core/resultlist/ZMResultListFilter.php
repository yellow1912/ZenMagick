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
 */
?>
<?php


/**
 * Base result list filter.
 *
 * @author DerManoMann
 * @package org.zenmagick.resultlist
 * @version $Id$
 */
class ZMResultListFilter extends ZMObject {
    protected $list_;
    protected $id_;
    protected $name_;
    protected $filterValues_;


    /**
     * Create a new result list filter.
     *
     * @param string id An optional filter id.
     * @param string name An optional filter name.
     */
    function __construct($id=null, $name='') {
        parent::__construct();

        $this->id_ = $id;
        $this->name_ = $name;
        $value = ZMRequest::getParameter($this->id_, '');
        $this->filterValues_ = explode(",", $value);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Set the result list we belong to.
     *
     * <p>This is important to be able to analyze the list to generate the list of all
     * available options (if based on the current data).</p>
     *
     * @param ZMResultList list The current result list.
     */
    function setResultList($list) { $this->list_ = $list; }

    /**
     * Filter the given list using the filters <code>exclude($obj)</code> method.
     *
     * @param array list The list to filter.
     * @return array The filtered list.
     */
    function filter($list) { 
        $remaining = array();
        foreach ($list as $obj) {
            if (!$this->exclude($obj)) {
                array_push($remaining, $obj);
            }
        }

        return $remaining;
    }

    /**
     * Return <code>true</code> if the given object is to be excluded.
     *
     * @param mixed obj The obecjt to examine.
     * @return boolean <code>true</code> if the object is to be excluded, <code>false</code> if not.
     */
    function exclude($obj) { return false; }

    /**
     * Returns <code>true</code> if this filter is currently active.
     *
     * @return boolean <code>true</code> if the filter is active, <code>false</code> if not.
     */
    function isActive() {
        return null != ZMRequest::getParameter($this->id_, null);
    }

    /**
     * Returns <code>true</code> if this filter supports multiple values as filter value.
     *
     * @return boolean <code>true</code> if multiple filter values are supported, <code>false</code> if not.
     */
    function isMultiSelection() { return false; }

    /**
     * Returns a list of active filter values.
     *
     * <p>If <code>isActive()</code> returns <code>false</code>, this list is guranteed to be empty.</p>
     *
     * @return array An array of string values.
     */
    function getSelectedValues() { return $this->filterValues_; }

    /**
     * Returns a list of all available filter values.
     *
     * @return array An array of string values.
     */
    function getOptions() { $options = array(); return $options; }

    /**
     * Returns <code>true</code> if this filter is avaialble for usage.
     *
     * <p>Filter might be configured but not be useful if there is for example only
     * one category or manufacturer to choose from.</p>
     *
     * @return boolean <code>true</code> if available, <code>false</code> if not.
     */
    function isAvailable() { return 1 < count($this->getOptions()); }

    /**
     * Returns the filters unique form field name.
     *
     * @return string The filters unique form field name.
     */
    function getId() { return $this->id_ . ($this->isMultiSelection() ? '[]' : ''); }

    /**
     * Returns the filter name.
     *
     * @return string The filter name.
     */
    function getName() { return $this->name_; }

}

?>
