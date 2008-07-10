<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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

    // load ZenMagick core
    $coreDir = dirname(dirname(__FILE__)) . '/core/';
    require $coreDir.'ZMLoader.php';
    ZMLoader::instance()->addPath($coreDir);
    ZMLoader::resolve('ZMObject');
    ZMLoader::resolve('ZMPhpPackagePacker');

    /**
     * Custom class for SimpleTest specific dependency handling.
     */
    class SimpleTestPacker extends ZMPhpPackagePacker {
        /**
         * {@inheritDoc}
         */
        public function ignoreFile($file) {
            return 'simpletest' != basename(dirname($file)) || false !== strpos($file, 'php4') || false !== strpos($file, 'eclipse') || false !== strpos($file, 'autorun') || false !== strpos($file, 'packed.php');
        }
    }

    $path = 'C:/Program Files/Apache Group/Apache2/htdocs/simpletest/';
    $version = trim(file_get_contents($path.'VERSION'));

    // pack; ideally path/version should be CLI args...
    $packer = new SimpleTestPacker($path, $path.'simpletest-'.$version.'.packed.php');
    $packer->setDebug(false);
    $packer->packFiles(true);

?>
