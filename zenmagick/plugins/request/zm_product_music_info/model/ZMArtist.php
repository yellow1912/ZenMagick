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
 */
?>
<?php


/**
 * An artist.
 *
 * @author mano
 * @package org.zenmagick.plugins.zm_product_music_info.model
 * @version $Id$
 */
class ZMArtist extends ZMObject {
    var $id_;
    var $name_;
    var $genre_;
    var $image_;
    var $url_;
    var $recordCompany_;


    /**
     * Create new instance.
     */
    function ZMArtist() {
        $this->id_ = 0;
        $this->name_ = '';
        $this->genre_ = '';
        $this->image_ = null;
        $this->url_ = null;
        $this->recordCompany_ = null;
    }

    /**
     * Create new instance.
     */
    function __construct() {
        $this->ZMArtist();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
    }


    /**
     * Get the artist id.
     *
     * @return int The artist id.
     */
    function getId() { return $this->id_; }

    /**
     * Get the artist name.
     *
     * @return string The artist name.
     */
    function getName() { return $this->name_; }

    /**
     * Get the name.
     *
     * @return string The genre.
     */
    function getGenre() { return $this->genre_; }

    /**
     * Check if an image is available.
     *
     * @return boolean <code>true</code> if an image is available, <code>false</code> if not.
     */
    function hasImage() { return !empty($this->image_); }

    /**
     * Get the artist image.
     *
     * @return string The artist image.
     */
    function getImage() { return $this->image_; }

    /**
     * Get the image info.
     *
     * @return ZMImageInfo The image info.
     */
    function getImageInfo() { return ZMLoader::make("ImageInfo", $this->image_, $this->name_); }

    /**
     * Check if a URL is available.
     *
     * @return boolean <code>true</code> if a URL is available, <code>false</code> if not.
     */
    function hasUrl() { return !empty($this->url_); }

    /**
     * Get the artist URL.
     *
     * @return string The artist URL.
     */
    function getUrl() { return $this->url_; }

    /**
     * Get the record company.
     *
     * @return ZMRecordCompany The record company.
     */
    function getRecordCompany() { return $this->recordCompany_; }

}

?>
