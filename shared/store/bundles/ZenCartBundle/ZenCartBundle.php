<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace zenmagick\apps\store\bundles\ZenCartBundle;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Routing\Loader\XmlFileLoader;

use Swift_Transport_SendmailTransport;

use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\utils\Executor;
use zenmagick\base\dependencyInjection\loader\YamlLoader;
use zenmagick\apps\store\bundles\ZenCartBundle\utils\EmailEventHandler;
use zenmagick\apps\store\menu\MenuLoader;

/**
 * Zencart support bundle.
 *
 * @author DerManoMann
 */
class ZenCartBundle extends Bundle {
    const ZENCART_ADMIN_FOLDER = 'ZENCART_ADMIN_FOLDER';

    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container) {
        parent::build($container);
    }

    /**
     * {@inheritDoc}
     */
    public function boot() {
        $settingsService = Runtime::getSettings();
        if (null == $settingsService->get('apps.store.zencart.path')) { // @todo or default to vendors/zencart?
            $settingsService->set('apps.store.zencart.path', dirname(Runtime::getInstallationPath()));
        }

        $eventDispatcher = Runtime::getEventDispatcher();
        $eventDispatcher->listen($this);
        $eventDispatcher->addListener('generate_email', array(Beans::getBean('zenmagick\apps\store\bundles\ZenCartBundle\utils\EmailEventHandler'), 'onGenerateEmail'));

        // random defines that we might need
        if (!defined('PRODUCTS_OPTIONS_TYPE_SELECT')) { define('PRODUCTS_OPTIONS_TYPE_SELECT', 0); }
        if (!defined('ATTRIBUTES_PRICE_FACTOR_FROM_SPECIAL')) { define('ATTRIBUTES_PRICE_FACTOR_FROM_SPECIAL', 0); }
        if (!defined('TEXT_PREFIX')) { define('TEXT_PREFIX', 'txt_'); }
        if (!defined('UPLOAD_PREFIX')) { define('UPLOAD_PREFIX', 'upload_'); }
    }

    /**
     * Prepare db config
     */
    public function onInitConfigDone($event) {
        $yaml = array('services' => array(
            'zenCartThemeStatusMapBuilder' => array('parent' => 'merge:themeStatusMapBuilder', 'class' => 'zenmagick\apps\store\bundles\ZenCartBundle\mock\ZenCartThemeStatusMapBuilder'),
            'zencartAutoLoader' => array('class' => 'zenmagick\apps\store\bundles\ZenCartBundle\utils\ZenCartAutoLoader'),
        ));
        $yamlLoader = new YamlLoader($this->container, new FileLocator(dirname(__FILE__)));
        $yamlLoader->load($yaml);

        if (!defined('DB_PREFIX')) define('DB_PREFIX', \ZMRuntime::getDatabase()->getPrefix());
        $settingsService = $this->container->get('settingsService');
        if (Runtime::isContextMatch('admin')) {
            $adminDir = $this->container->get('configService')->getConfigValue(self::ZENCART_ADMIN_FOLDER);
            if (null != $adminDir) {
                $settingsService->set('apps.store.zencart.admindir', $adminDir->getValue());
            }

            $urlMappings = __DIR__.'/Resources/config/admin/url_mappings.yaml';
            \ZMUrlManager::instance()->load(file_get_contents($urlMappings), false);

            $routingFile = __DIR__.'/Resources/config/admin/routing.xml';
            if (file_exists($routingFile)) {
                $routeResolver = $this->container->get('routeResolver');
                $routingLoader = new XmlFileLoader(new FileLocator());
                $routeCollection = $routingLoader->load($routingFile);
                $routeResolver->getRouter()->getRouteCollection()->addCollection($routeCollection);
            }

            if ($settingsService->get('zenmagick.http.request.secure')) {
                // make all of ZM admin secure
                $settingsService->set('zenmagick.http.request.allSecure', true);
            }
        }
        // include some zencart files we need.
        if (!defined('IS_ADMIN_FLAG')) { define('IS_ADMIN_FLAG', Runtime::isContextMatch('admin')); }
        include_once $settingsService->get('apps.store.zencart.path').'/includes/database_tables.php';
        $autoLoader = $this->container->get('zencartAutoLoader');
        $zcClassLoader = new ZenCartClassLoader();
        $zcClassLoader->setBaseDirectories($autoLoader->buildSearchPaths('includes/classes'));
        $zcClassLoader->register();
        // Set ZC classes used throughout
        $autoLoader->setGlobalValue('zco_notifier', new \notifier);
        $autoLoader->setGlobalValue('db', new \queryFactory);
        $autoLoader->setGlobalValue('messageStack', new \messageStack);
        $autoLoader->setGlobalValue('template', new \template_func);
        $autoLoader->setGlobalValue('sniffer', new \sniffer);


    }

    /**
     * Handle things that require a request.
     */
    public function onContainerReady($event) {
        $request = $event->get('request');
        include_once __DIR__.'/bridge/includes/configure.php';
        $autoLoader = $this->container->get('zencartAutoLoader');
        if (Runtime::isContextMatch('admin')) {
            // @todo shouldn't assume we already have a menu, but we have to since the $adminMenu is never checked for emptiness only null
            $adminMenu = $this->container->get('adminMenu');
            $menuLoader = new MenuLoader();
            $menuLoader->load(__DIR__.'/Resources/config/admin/menu.yaml', $adminMenu);

        } else {
            /**
             * only used in the orders class and old email functions
             * @todo move it somewhere else
             */
            $session = $request->getSession();
            if (null == $session->getValue('customers_ip_address')) {
                $session->setValue('customers_ip_address', $_SERVER['REMOTE_ADDR']);
            }

            $session->setValue('securityToken', $session->getToken());

            $autoLoader->setErrorLevel();

            $autoLoader->includeFiles('includes/version.php'); // used by the paypal modules!
            $autoLoader->includeFiles('includes/extra_configures/*.php');
            $autoLoader->includeFiles('includes/filenames.php');
            $autoLoader->includeFiles('includes/extra_datafiles/*.php');
            $autoLoader->includeFiles('includes/functions/extra_functions/*.php');
            $autoLoader->includeFiles('includes/functions/{functions_email.php,functions_general.php,html_output.php,functions_ezpages.php,password_funcs.php,sessions.php,zen_mail.php}');
            $autoLoader->includeFiles('includes/functions/banner.php');

            $autoLoader->setGlobalValue('currencies', new \currencies);

            if (null == $session->getValue('cart')) {
                $session->setValue('cart', new \shoppingCart);
            }
            if (null == $session->getValue('navigation')) {
                $session->setValue('navigation', new \navigationHistory);
            }
            $session->getValue('navigation')->add_current_page();
        }

        if (defined('EMAIL_TRANSPORT') && 'Qmail' == EMAIL_TRANSPORT && $this->container->has('swiftmailer.transport')) {
            if (null != ($transport = $this->container->get('swiftmailer.transport')) && $transport instanceof Swift_Transport_SendmailTransport) {
                $transport->setCommand('/var/qmail/bin/sendmail -t');
            }
        }
    }

    public function onDispatchStart($event) {
        if (Runtime::isContextMatch('storefront')) {
            $request = $event->get('request');
            $requestId = $request->getRequestId();
            // boot the rest of the ZenCart storefront code.
            $autoLoader = $this->container->get('zencartAutoLoader');
            // distribute these so they are only used where needed instead of globally.
            $globals = array(
                'PHP_SELF' => $_SERVER['PHP_SELF'],
                'cPath' => (string)$request->getCategoryPath(),
                'cPath_array' => $request->getCategoryPathArray(),
                'code_page_directory' => DIR_WS_INCLUDES.'modules/pages/'.$requestId,
                'current_category_id' => $request->getCategoryId(),
                'current_page_base' => $requestId,
                // needed by require_languages.php to load per page language files (inside page header_php.php files)
                'page_directory' => DIR_WS_INCLUDES.'modules/pages/'.$requestId,
                'request_type' => $request->isSecure ? 'SSL' : 'NONSSL',
                'session_started' => true,
            );
            $autoLoader->setGlobalValues($globals);

            // @todo use overrideGlobals() from symfony request component
            $_GET['main_page'] = $requestId; // needed (somewhere) to catch routes from the route resolver
            foreach (array_keys($_GET) as $v) {
                $_GET[$v] = $request->getParameter($v);
            }
            foreach (array_keys($_POST) as $v) {
                $_POST[$v] = $request->getParameter($v);
            }

            $themeId = $this->container->get('themeService')->getActiveThemeId();

            $autoLoader->setErrorLevel();
            define('DIR_WS_TEMPLATE', DIR_WS_TEMPLATES.$themeId.'/');
            define('DIR_WS_TEMPLATE_IMAGES', DIR_WS_TEMPLATE.'images/');
            define('DIR_WS_TEMPLATE_ICONS', DIR_WS_TEMPLATE_IMAGES.'icons/');
            $autoLoader->setGlobalValue('template_dir', $themeId);

            $cwd = getcwd();
            chdir($this->container->get('settingsService')->get('apps.store.zencart.path'));
            // required for the payment,checkout,shipping modules
            $autoLoader->includeFiles('includes/classes/db/mysql/define_queries.php');
            $autoLoader->includeFiles('includes/languages/%template_dir%/%language%.php');
            $autoLoader->includeFiles('includes/languages/%language%.php');
            $autoLoader->includeFiles(array(
                'includes/languages/%language%/extra_definitions/%template_dir%/*.php',
                'includes/languages/%language%/extra_definitions/*.php')
            );
            chdir($cwd);
            $autoLoader->restoreErrorLevel();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function onControllerProcessStart($event) {
        if (!Runtime::isContextMatch('storefront')) return;
        $request = $event->get('request');

        if ($this->isZencartTheme($request) && null != ($dispatcher = $request->getDispatcher())) {
            $settingsService = $this->container->get('settingsService');
            $settingsService->set('zenmagick.http.view.defaultLayout', null);
            $executor = new Executor(array($this->container->get('zenmagick\apps\store\bundles\ZenCartBundle\controller\ZencartStorefrontController'), 'process'), array($request));
            $dispatcher->setControllerExecutor($executor);
        }
    }

    /**
     * Check for zencart theme.
     */
    protected function isZencartTheme($request) {
        $languageId = $request->getSession()->getLanguageId();
        $themeService = $this->container->get('themeService');
        $themeChain = $themeService->getThemeChain($languageId);
        foreach ($themeChain as $theme) {
            $meta = $theme->getConfig('meta');
            if (array_key_exists('zencart', $meta)) {
                return true;
            }
        }
        return false;
    }
}
