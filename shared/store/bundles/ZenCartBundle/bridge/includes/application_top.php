<?php
/**
 * application_top.php Common actions carried out at the start of each page invocation.
 *
 * Initializes common classes & methods. Controlled by an array which describes
 * the elements to be initialised and the order in which that happens.
 * see {@link  http://www.zen-cart.com/wiki/index.php/Developers_API_Tutorials#InitSystem wikitutorials} for more details.
 *
 * @package initSystem
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: application_top.php 19731 2011-10-09 17:20:30Z wilt $
 */

use zenmagick\base\Runtime;

if (!class_exists('zenmagick\base\Application')) {
    include 'zenmagick/init.php';
}
$autoLoader = Runtime::getContainer()->get('zencartAutoLoader');
$autoLoadConfig = array();
$loaderPrefix = isset($loaderPrefix) ? $loaderPrefix : 'config';
$coreLoaderPrefix = in_array($loaderPrefix, array('config', 'paypal_ipn')) ? 'config' : $loaderPrefix;
$files = $autoLoader->resolveFiles('includes/auto_loaders/'.$coreLoaderPrefix.'.*.php');
if ($coreLoaderPrefix == 'config')) {
    unset($files[$coreLoaderPrefix.'.core.php']);
}

foreach ($files as $file) {
    include $file;
}

require Runtime::getInstallationPath().'/shared/store/bundles/ZenCartBundle/bridge/includes/autoload_func.php';
