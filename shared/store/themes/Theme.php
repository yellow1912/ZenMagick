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
namespace zenmagick\apps\store\themes;

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;
use zenmagick\base\dependencyInjection\loader\YamlLoader;
use zenmagick\apps\store\utils\ContextConfigLoader;

use Symfony\Component\Config\FileLocator;

/**
 * A theme.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class Theme extends ZMObject {
    private $id;
    private $config;
    private $basePath;
    private $locales;

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->id = null;
        $this->config = array();
        $this->basePath = null;
        $this->locales = array();
    }

    /**
     * Set the theme id.
     *
     * @param string id The theme id.
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * Get the themes id.
     *
     * @return string The theme id.
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set the full path to the themes base directory.
     *
     * @param string path The path.
     */
    public function setBasePath($path) {
        $this->basePath = $path;;
    }

    /**
     * Add a locale code supported by this theme.
     *
     * @param string code The locale code.
     */
    public function addLocale($code) {
        $this->locales[$code] = $code;
    }

    /**
     * Set locale codes for all locale supported by this theme.
     *
     * @param array locales The locale codes.
     */
    public function setLocales(array $locales) {
        $this->locales = $locales;
    }

    /**
     * Get locale codes for all locale supported by this theme.
     *
     * @return array The locale codes.
     */
    public function getLocales() {
        return $this->locales;
    }

    /**
    /**
     * Return the full path to the themes base directory.
     *
     * @return string The theme base directory.
     */
    public function getBasePath() {
        return $this->basePath;
    }

    /**
     * Get theme config.
     *
     * @param string key Optional config key; default is <code>null</code> to return the full map.
     * @return mixed Theme config map, the value of a specific key or <code>null</code> for unknown keys.
     */
    public function getConfig($key=null) {
        if (null == $key) {
            return $this->config;
        }

        if (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }

        return null;
    }

    /**
     * Set theme name.
     *
     * @return string The name.
     */
    public function getName() {
        return array_key_exists('name', $this->config['meta']) ? $this->config['meta']['name'] : '??';
    }

    /**
     * Get the meta data.
     *
     * @return array The meta data.
     */
    public function getMeta() {
        return $this->config['meta'];
    }

    /**
     * Set full theme config.
     *
     * @param array config The new config map.
     */
    public function setConfig($config) {
        $this->config = $config;
    }

    /**
     * Set theme config value.
     *
     * @param mixed key The config key or an array to set all.
     * @param mixed value The value.
     */
    public function setConfigValue($key, $value) {
        if (is_array($key)) {
            $this->config = $key;
            return;
        }
        $this->config[$key] = $value;
    }

    /**
     * Return the path of the extra directory.
     *
     * @return string A full filename denoting the themes extra directory.
     * @deprecated
     */
    public function getExtraDir() {
        return $this->getBasePath() . '/extra';
    }

    /**
     * Return the path of the boxes directory.
     *
     * @return string A full filename denoting the themes boxes directory.
     */
    public function getBoxesDir() {
        return $this->getBasePath() . '/content/boxes';
    }

    /**
     * Return the path of the template directory.
     *
     * @return string A full path to the theme's template folder.
     */
    public function getTemplatePath() {
        return $this->getBasePath() . '/content';
    }

    /**
     * Return the path of the resources directory.
     *
     * @return string A full path to the theme's resources folder.
     */
    public function getResourcePath() {
        return $this->getBasePath() . '/content';
    }

    /**
     * Return the path of the content directory.
     *
     * @return string A full filename denoting the themes content directory.
     * @deprecated use getTemplatePath() or getResourcePath() instead
     */
    public function getContentDir() {
        return $this->getBasePath() . '/content';
    }

    /**
     * Get a list of available static pages.
     *
     * @param boolean includeDefaults If set to <code>true</code>, default pages will be included; default is <code>false</code>.
     * @param int languageId Language id.
     * @return array List of available static page names.
     */
    public function getStaticPageList($includeDefaults=false, $languageId) {
        $language = $this->container->get('languageService')->getLanguageForId($languageId);
        $languageDir = $language->getDirectory();
        $path = $this->getBasePath() . '/lang'.$languageDir."/".'static/';

        $pages = array();
        if (is_dir($path)) {
            $handle = @opendir($path);
            while (false !== ($file = readdir($handle))) {
                if (!\ZMLangUtils::endsWith($file, '.php')) {
                    continue;
                }
                $page = str_replace('.php', '', $file);
                $pages[$page] = $page;
            }
            @closedir($handle);
        }

        if ($includeDefaults) {
            // TODO: deprecated
            $path = $this->container->get('themeService')->getThemesDir().Runtime::getSettings()->get('apps.store.themes.default').'/lang/'.$languageDir.'/static/';
            if (is_dir($path)) {
                $handle = @opendir($path);
                while (false !== ($file = readdir($handle))) {
                    if (!\ZMLangUtils::endsWith($file, '.php')) {
                        continue;
                    }
                    $page = str_replace('.php', '', $file);
                    $pages[$page] = $page;
                }
                @closedir($handle);
            }
        }
        return $pages;
    }

    /**
     * Write the content of a static (define) page.
     *
     * @param string page The page name.
     * @param string contents The contents.
     * @param int languageId Language id.
     * @return boolean The status.
     */
    public function saveStaticPageContent($page, $contents, $languageId) {
        $language = $this->container->get('languageService')->getLanguageForId($languageId);
        $languageDir = $language->getDirectory();
        $path = $this->getBasePath() . '/lang'.$languageDir.'/static/';
        if (!file_exists($path)) {
            $this->container->get('filesystem')->mkdir($path, 0755);
        }
        $filename = $path.$page.'.php';

        if (file_exists($filename)) {
            if (file_exists($filename.'.bak')) {
                @unlink($filename.'.bak');
            }
            @rename($filename, $filename.'.bak');
        }
        $handle = fopen($filename, 'w');
        fwrite($handle, $contents, strlen($contents));
        fclose($handle);
        \ZMFileUtils::setFilePerms($filename);

        return file_exists($filename);
    }

    /**
     * Get the content of a static (define) page.
     *
     * @param string page The page name.
     * @param int languageId Language id.
     * @return string The content or <code>null</code>.
     */
    public function staticPageContent($page, $languageId) {
        if (Runtime::getSettings()->get('apps.store.staticContent', false)) {
            if (null != ($ezPage = $this->container->get('ezPageService')->getPageForName($page, $languageId))) {
                return $ezPage->getHtmlText();
            }
            return null;
        }
        $language = $this->container->get('languageService')->getLanguageForId($languageId);
        $languageDir = $language->getDirectory();
        $path = $this->getBasePath() . '/lang'.$languageDir.'/static/';

        $filename = $path.$page.'.php';
        if (!file_exists($filename)) {
            return null;
        }

        $request = $this->container->get('request');
        $settings = $this->container->get('settingsService');
        $contents = @file_get_contents($filename);
        // allow PHP
        ob_start();
        eval('?>'.$contents);
        return ob_get_clean();
    }

    /**
     * Load locale (l10n/i18n).
     *
     * @param Language language The language.
     */
    public function loadLocale($language) {
        if (null === $language) {
            // this may happen if the i18n patch hasn't been updated
            $language = $this->container->get('languageService')->getDefaultLanguage();
        }

        $code = $language->getCode();
        $path = $this->getBasePath().'/locale/'.$code;

        // re-init with next file
        $this->container->get('localeService')->getLocale()->init($code, $path);
    }

    /**
     * Load additional theme config settins from <em>theme.yaml</em>.
     */
    public function loadSettings() {
        $configLoader = $this->container->get('contextConfigLoader');
        $configLoader->apply($this->config);
    }

}
