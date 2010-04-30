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
 * Product search.
 *
 * <p>Sorting and filtering is based on the corresponding result list support classes.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.store.utils
 * @version $Id$
 */
class ZMProductFinder {
    protected $criteria_;
    protected $sortId_;
    protected $descending_;


    /**
     * Create a new instance.
     *
     * @param ZMSearchCriteria criteria Optional search criteria; default is <code>null</code>.
     */
    function __construct($criteria=null) {
        $this->criteria_ = $criteria;
        $this->sortId_ = null;
        $this->descending_ = false;
    }


    /**
     * Set the search criteria.
     *
     * @param ZMSearchCriteria criteria Search criteria.
     */
    public function setCriteria($criteria) {
        $this->criteria_ = $criteria;
    }

    /**
     * Set the descending flag.
     *
     * @param boolean descending The new value.
     */
    public function setDescending($descending) {
        $this->descending_ = $descending;
    }

    /**
     * Set the sort id.
     *
     * @param string sortId The sort id.
     */
    public function setSortId($sortId) {
        $this->sortId_ = $sortId;
    }

    /**
     * Execute a product search for the given criteria.
     *
     * @return ZMQueryDetails Query details for a product id search.
     */
    public function execute() {
        $queryDetails = $this->buildQuery($this->criteria_);
        return $queryDetails;
    }

    /**
     * Build the search SQL.
     *
     * @param ZMSearchCriteria criteria Search criteria.
     * @return ZMQueryDetails The search SQL.
     */
    protected function buildQuery($criteria) {
        $args = array();

        $select = "SELECT DISTINCT p.products_id";
        if ($criteria->isIncludeTax() && (!ZMLangUtils::isEmpty($criteria->getPriceFrom()) || !ZMLangUtils::isEmpty($criteria->getPriceTo()))) {
            $select .= ", SUM(tr.tax_rate) AS tax_rate";
        }

        $from = " FROM (" . TABLE_PRODUCTS . " p 
                 LEFT JOIN " . TABLE_MANUFACTURERS . " m USING(manufacturers_id), " . 
                 TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_CATEGORIES . " c, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c)
                 LEFT JOIN " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " mtpd ON mtpd.products_id= p2c.products_id AND mtpd.language_id = :languageId";

        $args['languageId'] = $criteria->getLanguageId();

        if ($criteria->isIncludeTax() && (!ZMLangUtils::isEmpty($criteria->getPriceFrom()) || !ZMLangUtils::isEmpty($criteria->getPriceTo()))) {
            $from .= " LEFT JOIN " . TABLE_TAX_RATES . " tr ON p.products_tax_class_id = tr.tax_class_id
                       LEFT JOIN " . TABLE_ZONES_TO_GEO_ZONES . " gz ON tr.tax_zone_id = gz.geo_zone_id
                         AND (gz.zone_country_id IS null OR gz.zone_country_id = 0 OR gz.zone_country_id = :zoneId)
                         AND (gz.zone_id IS null OR gz.zone_id = 0 OR gz.zone_id = :zoneId)";
            $args['countryId'] = $criteria->getCountryId();
            $args['zoneId'] = $criteria->getZoneId();
        }

        $where = " WHERE (p.products_status = 1 AND p.products_id = pd.products_id AND pd.language_id = :languageId
                     AND p.products_id = p2c.products_id AND p2c.categories_id = c.categories_id";

        $args['languageId'] = $criteria->getLanguageId();

        if (0 != $criteria->getCategoryId()) {
            if ($criteria->isIncludeSubcategories()) {
                $where .= " AND p2c.products_id = p.products_id
                            AND p2c.products_id = pd.products_id
                            AND p2c.categories_id in (:categoryId)";
                $category = ZMCategories::instance()->getCategoryForId($criteria->getCategoryId());
                $args['categoryId'] = $category->getChildIds();
            } else {
                $where .= " AND p2c.products_id = p.products_id
                            AND p2c.products_id = pd.products_id
                            AND pd.language_id = :languageId
                            AND p2c.categories_id = :categoryId";
                $args['categoryId'] = $criteria->getCategoryId();
                $args['languageId'] = $criteria->getLanguageId();
            }
        }

        if (0 != $criteria->getManufacturerId()) {
            $where .= " AND m.manufacturers_id = :manufacturerId";
            $args['manufacturerId'] = $criteria->getManufacturerId();
        }

        if (!ZMLangUtils::isEmpty($criteria->getKeywords())) {
            if (zen_parse_search_string(stripslashes($criteria->getKeywords()), $tokens)) {
                $index = 0;
                $where .= " AND (";
                foreach ($tokens as $token) {
                    switch ($token) {
                    case '(':
                    case ')':
                    case 'and':
                    case 'or':
                        $where .= " " . $token . " ";
                        break;
                    default:
                        // use name for all string operations
                        $name = ++$index.'#name';
                        $args[$name] = '%'.$token.'%';

                        $where .= "(pd.products_name LIKE :".$name." OR p.products_model LIKE :".$name." OR m.manufacturers_name LIKE :".$name."";
                        // search meta tags
                        $where .= " OR (mtpd.metatags_keywords LIKE :".$name." AND mtpd.metatags_keywords !='')";
                        $where .= " OR (mtpd.metatags_description LIKE :".$name." AND mtpd.metatags_description !='')";
                        if ($criteria->isIncludeDescription()) {
                            $where .= " OR pd.products_description LIKE :".$name."";
                        }
                        $where .= ')';
                        break;
                    }
                }
                $where .= ")";
            }
        }
        $where .= ')';

        if (!ZMLangUtils::isEmpty($criteria->getDateFrom())) {
            $where .= " AND p.products_date_added >= :1#dateAdded";
            $args['1#dateAdded'] = ZMTools::translateDateString($criteria->getDateFrom(), UI_DATE_FORMAT, ZM_DATETIME_FORMAT);
        }

        if (!ZMLangUtils::isEmpty($criteria->getDateTo())) {
            $where .= " AND p.products_date_added <= :2#dateAdded";
            $args['2#dateAdded'] = ZMTools::translateDateString($criteria->getDateTo(), UI_DATE_FORMAT, ZM_DATETIME_FORMAT);
        }

        if ($criteria->isIncludeTax()) {
            if (!ZMLangUtils::isEmpty($criteria->getPriceFrom())) {
                $where .= " AND (p.products_price_sorter * IF(gz.geo_zone_id IS null, 1, 1 + (tr.tax_rate / 100)) >= :1#productPrice)";
                $args['1#productPrice'] = $criteria->getPriceFrom();
            }
            if (!ZMLangUtils::isEmpty($criteria->getPriceTo())) {
                $where .= " AND (p.products_price_sorter * IF(gz.geo_zone_id IS null, 1, 1 + (tr.tax_rate / 100)) <= :2#productPrice)";
                $args['2#productPrice'] = $criteria->getPriceTo();
            }
        } else {
            if (!ZMLangUtils::isEmpty($criteria->getPriceFrom())) {
                $where .= " AND (p.products_price_sorter >= :1#productPrice)";
                $args['1#productPrice'] = $criteria->getPriceFrom();
            }
            if (!ZMLangUtils::isEmpty($criteria->getPriceTo())) {
                $where .= " AND (p.products_price_sorter <= :2#productPrice)";
                $args['2#productPrice'] = $criteria->getPriceTo();
            }
        }

        if ($criteria->isIncludeTax() && (!ZMLangUtils::isEmpty($criteria->getPriceFrom()) || !ZMLangUtils::isEmpty($criteria->getPriceTo()))) {
            $where .= " GROUP BY p.products_id, tr.tax_priority";
        }

        $sort = ' ORDER BY';
        if (null !== $this->sortId_) {
            switch ($this->sortId_) {
            case 'model':
                $sort .= " p.products_model " . ($this->descending_ ? "DESC" : "") . ", pd.products_name";
                break;
            case 'name':
                $sort .= " pd.products_name " . ($this->descending_ ? "DESC" : "");
                break;
            case 'manufacturer':
                $sort .= " m.manufacturers_name " . ($this->descending_ ? "DESC" : "") . ", pd.products_name";
                break;
            case 'price':
                $sort .= " p.products_price_sorter " . ($this->descending_ ? "DESC" : "") . ", pd.products_name";
                break;
            case 'weight':
                $sort .= " p.products_weight " . ($this->descending_ ? "DESC" : "") . ", pd.products_name";
                break;
            default:
                ZMLogging::instance()->log('invalid sort id: ' . $this->sortId_, ZMLogging::WARN);
               $sort .= " p.products_sort_order,  pd.products_name";
               break;
            }
        } else {
            $sort .= " p.products_sort_order,  pd.products_name";
        }

        $sql = $select . $from . $where . $sort;
        $tables = array(TABLE_PRODUCTS, TABLE_PRODUCTS_DESCRIPTION, TABLE_MANUFACTURERS, TABLE_CATEGORIES, TABLE_TAX_RATES, TABLE_ZONES_TO_GEO_ZONES);
        return new ZMQueryDetails(Runtime::getDatabase(), $sql, $args, $tables, null, 'p.products_id');
    }

}