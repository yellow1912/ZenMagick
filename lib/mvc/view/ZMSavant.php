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
 * Custom Savant(3).
 *
 * <p>Adds some convenience methods to access templates.</p>
 *
 * <p><strong>ATTENTION:</strong> These methods only make sense if called from
 * within a template.</p>
 *
 * <p>Also, adds support for caching. The config map supports a key <em>cache</em> that
 * is expected to be a class name that implements the following two methods:</p>
 * <dl>
 *   <dt><code>lookup($tpl)</code></dt>
 *   <dd>Query the cache for the given template name and return the cached contents (if any).
 *     If the template is not cached (yet), or is not allowed to be cached, <code>null</code>
 *     should be returned.</dd>
 *   <dt><code>save($tpl, $contents)</code></dt>
 *   <dd>Save the contents of the given template fetch in the cache (if allowed).</dd>
 * </dl>
 *
 * <p>It should be noted that it is the reponsibility of the cache class to decide whether a given
 * template can be cached or not.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.view
 */
class ZMSavant extends Savant3 {

    /**
     * Create a new instance.
     */
    function __construct($config=null) {
        parent::__construct($config);
        if (isset($config['cache'])) {
            $this->__config['cache'] = $config['cache'];
        }
        if (isset($this->__config['cache']) && !is_object($this->__config['cache'])) {
            $this->__config['cache'] = ZMLoader::make($this->__config['cache']);
        }
        // why isn't that set in Savant3???
        if (isset($config['compiler'])) {
            $this->__config['compiler'] = $config['compiler'];
        }
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        // no parent destructor!!
    }


    /**
     * Check if the given template/resource file exists.
     *
     * @param string filename The filename, relative to the template path.
     * @param string type The lookup type; valid values are <code>ZMView::TEMPLATE</code> and <code>ZMView::RESOURCE</code>;
     *  default is <code>ZMVIew::TEMPLATE</code>.
     * @return boolean <code>true</code> if the file exists, <code>false</code> if not.
     */
    public function exists($filename, $type=ZMView::TEMPLATE) {
        return !ZMLangUtils::isEmpty($this->findFile($type, $filename));
    }

    /**
     * Resolve the given templates filename to a fully qualified filename.
     *
     * @param string filename The filename, relative to the template path.
     * @param string type The lookup type; valid values are <code>ZMView::TEMPLATE</code> and <code>ZMView::RESOURCE</code>;
     *  default is <code>ZMVIew::TEMPLATE</code>.
     * @return string A fully qualified filename or <code>null</code>.
     */
    public function path($filename, $type=ZMView::TEMPLATE) {
        $path = $this->findFile($type, $filename);
        return ZMLangUtils::isEmpty($path) ? null : $path;
    }

    /**
     * Resolve the given (relative) templates filename into a url.
     *
     * @param string filename The filename, relative to the template path.
     * @param string type The lookup type; valid values are <code>ZMView::TEMPLATE</code> and <code>ZMView::RESOURCE</code>;
     *  default is <code>ZMVIew::TEMPLATE</code>.
     * @return string A url.
     */
    public function asUrl($filename, $type=ZMView::TEMPLATE) {
        if (null != ($path = $this->findFile($type, $filename))) {
            $basePath = ZMVIEW::TEMPLATE == $type ? $this->request->getTemplatePath() : $this->request->getWebPath();
            $relpath = str_replace($basePath, '', $path);
            if ($relpath != $path) {
                // only if matched and replaced...
                // now convert to URL...
                $relpath = str_replace('\\', '/', $relpath);
                $url = $this->request->absoluteURL($relpath);
                ZMLogging::instance()->log('resolve filename '.$filename.' (type='.$type.') as url: '.$url.'; relpath='.$relpath, ZMLogging::TRACE);
                return $url;
            }
        }

        ZMLogging::instance()->log('can\'t resolve filename '.$filename.' (type='.$type.') '.$filename.' to url', ZMLogging::WARN);
        return '';
    }

    /**
     * {@inheritDoc}
     *
     * Adds a hook for flexible caching.
     */
    public function fetch($tpl=null) {
        // check if caching enabled
        if (isset($this->__config['cache'])) {
            // check for cache hit
            if (null != ($result = call_user_func(array($this->__config['cache'], 'lookup'), $tpl))) {
                return $result;
            }
        }

        // generate content as usual
        $result = parent::fetch($tpl);

        if (isset($this->__config['cache'])) {
            // offer to cache the result
            call_user_func(array($this->__config['cache'], 'save'), $tpl, $result);
        }

        return $result;
    }

    /**
     * Call the block handler for the given block id.
     *
     * <p>All registered block contents that is found at this time will be returned as ready-to-use HTML.</p>
     *
     * @param string blockId The block id.
     * @param array args Optional parameter; default is an empty array.
     * @return string The HTML content for this block.
     */
    public function block($blockId, $args=array()) {
        return '';
    }

}