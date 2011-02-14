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

define('_ZM_ZEN_APP_BOTTOM_PHP', DIR_FS_CATALOG.DIR_WS_INCLUDES."application_bottom.php");

/**
 * Patch to enable ZenMagick without themes.
 *
 * @author DerManoMann
 * @package zenmagick.store.admin.installation.patches.file
 */
class ZMNoThemeSupportPatch extends ZMFilePatch {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('noThemeSupport');
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Checks if this patch can still be applied.
     *
     * @param array lines The file contents of <code>index.php</code>.
     * @return boolean <code>true</code> if this patch can still be applied.
     */
    function isOpen($lines=null) {
        if (null == $lines) {
            $lines = $this->getFileLines(_ZM_ZEN_APP_BOTTOM_PHP);
        }

        // look for ZenMagick code...
        $storeInclude = false;
        foreach ($lines as $line) {
            if (false !== strpos($line, 'getEventDispatcher')) {
                $storeInclude = true;
            }
        }

        return !($storeInclude);
    }

    /**
     * Checks if this patch is ready to be applied.
     *
     * @return boolean <code>true</code> if this patch is ready and all preconditions are met.
     */
    function isReady() {
        return is_writeable(_ZM_ZEN_APP_BOTTOM_PHP);
    }

    /**
     * Get the precondition message.
     *
     * <p>This will return an empty string when <code>isReady()</code> returns <code>true</code>.</p>
     *
     * @return string The preconditions message or an empty string.
     */
    function getPreconditionsMessage() {
        return $this->isReady() ? "" : "Need permission to write " . _ZM_ZEN_APP_BOTTOM_PHP;
    }

    /**
     * Execute this patch.
     *
     * @param boolean force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
        $lines = $this->getFileLines(_ZM_ZEN_APP_BOTTOM_PHP);
        if (!$this->isOpen($lines)) {
            return true;
        }

        $PATCHLINE = "if (!ZMSettings::get('isEnableZMThemes')) { \$_zm_contents = \\zenmagick\\base\\Runtime::getEventDispatcher()->filter(new \\zenmagick\\base\\events\\\Event(null, 'finalise_contents'), ob_get_clean()); echo \$_zm_contents; ZMRequest::instance()->getSession()->clearMessages(); \\zenmagick\\base\\Runtime::getEventDispatcher()->notify(new \\zenmagick\\base\\events\\\Event(null, 'all_done', array('request' => ZMRequest::instance()))); } /* added by ZenMagick installation patcher */";

        if (is_writeable(_ZM_ZEN_APP_BOTTOM_PHP)) {
            $patchedLines = array();
            foreach ($lines as $line) {
                if (false !== strpos($line, "session_write_close")) {
                    array_push($patchedLines, $PATCHLINE);
                }
                $patchedLines[] = $line;
            }
            return $this->putFileLines(_ZM_ZEN_APP_BOTTOM_PHP, $patchedLines);
        } else {
            ZMLogging::instance()->log("** ZenMagick: no permission to patch no theme support into application_bottpm.php", ZMLogging::ERROR);
            return false;
        }
    }

    /**
     * Revert the patch.
     *
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function undo() {
        $lines = $this->getFileLines(_ZM_ZEN_APP_BOTTOM_PHP);
        if ($this->isOpen($lines)) {
            return true;
        }

        if (is_writeable(_ZM_ZEN_APP_BOTTOM_PHP)) {
            $unpatchedLines = array();
            foreach ($lines as $line) {
                if (false !== strpos($line, "finalise_contents") && false !== strpos($line, "getEventDispatcher")) {
                    continue;
                }
                $unpatchedLines[] = $line;
            }

            return $this->putFileLines(_ZM_ZEN_APP_BOTTOM_PHP, $unpatchedLines);
        } else {
            ZMLogging::instance()->log("** ZenMagick: no permission to patch application_bottpm.php for uninstall", ZMLogging::ERROR);
            return false;
        }

        return true;
    }

}
