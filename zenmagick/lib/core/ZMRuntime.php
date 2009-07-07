<?php
/*
 * ZenMagick Core - Another PHP framework.
 * Copyright (C) 2006,2009 ZenMagick
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
 * Central place for runtime stuff.
 *
 * <p>This is kind of the <em>application context</em>.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.core
 * @version $Id: ZMRuntime.php 2265 2009-06-16 05:32:36Z DerManoMann $
 */
class ZMRuntime extends ZMObject {
    private static $databaseMap_ = array();


    /**
     * Get the database (provider).
     *
     * <p><code>ZMDatabase</code> instances are cached, based on the given <code>$conf</code> data.</p>
     *
     * <p>Supported keys for <em>$conf</em> are:</p>
     * <dl>
     *  <dt>driver</dt>
     *  <dd>The database driver/type; default is <code>mysql</code>.</dd>
     *  <dt>host</dt>
     *  <dd>The database host; default is <code>DB_SERVER</code>.</dd>
     *  <dt>port</dt>
     *  <dd>The database port; optional, no default.</dd>
     *  <dt>username</dt>
     *  <dd>The database username; default is <code>DB_SERVER_USERNAME</code>.</dd>
     *  <dt>password</dt>
     *  <dd>The database password; default is <code>DB_SERVER_PASSWORD</code>.</dd>
     *  <dt>database</dt>
     *  <dd>The database name; default is <code>DB_DATABASE</code>.</dd>
     *  <dt>provider</dt>
     *  <dd>The requested implementation class; if omotted, this defaults to
     *   <code>ZMSettings::get('zenmagick.core.database.provider')</code>.</dd>
     *  <dt>initQuery</dt>
     *  <dd>An optional init query to execute; useful to set the character encoding, etc.; default is <code>null</code>.</dd>
     * </dl>
     *
     * <p>If the given parameter <code>$conf</code> is a string, the method will
     * lookup database settings using a settings key build like:  <em>zenmagick.core.database.connections.[<code>$conf</code>]</em>.</p>
     *
     * @param mixed conf Optional configuration; either an array with any of the supported keys, or a string; default is <em>default</em>.
     * @return ZMDatabase A <code>ZMDatabase</code> implementation.
     */
    public static function getDatabase($conf='default') { 
        if (is_string($conf)) {
            $dbconf = ZMLangUtils::toArray(ZMSettings::get('zenmagick.core.database.connections.'.$conf));
        } else {
            $default = ZMLangUtils::toArray(ZMSettings::get('zenmagick.core.database.connections.default'));
            $dbconf = array_merge($default, $conf);
        }

        ksort($dbconf);
        $key = serialize($dbconf);
        if (!array_key_exists($key, self::$databaseMap_)) {
            $provider = array_key_exists('provider', $dbconf) ? $dbconf['provider'] : ZMSettings::get('zenmagick.core.database.provider');
            self::$databaseMap_[$key] = ZMLoader::make($provider, $dbconf);
        }

        return self::$databaseMap_[$key];
    }

    /**
     * Get the full ZenMagick installation path.
     *
     * @return string The ZenMagick installation folder.
     */
    public static function getInstallationPath() { 
        //XXX: watch this!
        return defined('ZM_BASE_DIR') ? constant('ZM_BASE_DIR') : dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR;
    }

    /**
     * Return the plugin base directory.
     *
     * <p>May be configured via the setting <em></em>. Default is <em>../lib/plugins</em>.</p>
     * @return string The base directory for plugins.
     */
    public static function getPluginsDirectory() { 
        return ZMSettings::get('zenmagick.core.plugins.baseDir', dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR);
    }

    /**
     * Get the currently elapsed page execution time.
     *
     * @param string time Optional execution timestamp to be used instead of the current time.
     * @return long The execution time in milliseconds.
     */
    public static function getExecutionTime($time=null) {
        $startTime = explode (' ', ZM_START_TIME);
        $endTime = explode (' ', (null!=$time?$time:microtime()));
        $executionTime = $endTime[1]+$endTime[0]-$startTime[1]-$startTime[0];
        return round($executionTime, 4);
    }

}

?>
