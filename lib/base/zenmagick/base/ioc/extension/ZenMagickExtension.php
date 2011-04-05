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
namespace zenmagick\base\ioc\extension;

use zenmagick\base\Runtime;
use zenmagick\base\ioc\loader\YamlLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * ZenMagick extension.
 *
 * @author DerManoMann
 * @package zenmagick.base.ioc.extension
 */
class ZenMagickExtension extends Extension {

    /**
     * Load ZenMagick <em>settings</em> and treat as DI parameters.
     *
     * @param array config An array of settings values.
     * @param ContainerBuilder configuration A <code>ContainerBuilder</code> instance.
     */
    public function settingsLoad(array $config, ContainerBuilder $container) {
        $loader = new YamlLoader($container);
        $loader->setParameters(array('zenmagick' => $config));
    }

    /**
     * Load apps settings and treat as DI parameters.
     *
     * @param array config Config.
     * @param ContainerBuilder configuration A <code>ContainerBuilder</code> instance.
     */
    public function appsLoad(array $config, ContainerBuilder $container) {
        $loader = new YamlLoader($container);
        $loader->setParameters(array('apps' => $config));
    }

    /**
     * Load doctrine settings and treat as DI parameters.
     *
     * @param array config Config.
     * @param ContainerBuilder configuration A <code>ContainerBuilder</code> instance.
     */
    public function doctrineLoad(array $config, ContainerBuilder $container) {
        $loader = new YamlLoader($container);
        $loader->setParameters(array('doctrine' => $config));
    }

    /**
     * {@inheitDoc}
     */
    public function getXsdValidationBasePath() {
        return null;
    }

    /**
     * {@inheitDoc}
     */
    public function getNamespace() {
        return 'http://www.zenmagick.org/schema/zenmagick';
    }

    /**
     * {@inheitDoc}
     */
    public function getAlias() {
        return 'zenmagick';
    }

}
