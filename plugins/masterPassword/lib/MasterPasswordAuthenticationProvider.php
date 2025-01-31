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
namespace zenmagick\plugins\masterPassword;

use zenmagick\base\ZMObject;
use zenmagick\base\security\authentication\AuthenticationProvider;

/**
 * Master password authentication provider.
 *
 * <p>This provider is intended only for validating passwords. Encrypting will be delegated to the configured default provider.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class MasterPasswordAuthenticationProvider extends ZMObject implements AuthenticationProvider {

    /**
     * {@inheritDoc}
     */
    public function encryptPassword($plaintext, $salt=null) {
        return $this->container->get('authenticationManager')->getDefaultProvider()->encryptPassword($plaintext, $salt);
    }

    /**
     * {@inheritDoc}
     */
    public function validatePassword($plaintext, $encrypted) {
        $masterPassword = $this->container->get('pluginService')->getPluginForId('masterPassword')->get('masterPassword');
        return !empty($masterPassword) && $this->container->get('authenticationManager')->getDefaultProvider()->validatePassword($plaintext, $masterPassword);
    }

}
