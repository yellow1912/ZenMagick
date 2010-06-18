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

    // build full zen-cart menu
    ZMAdminMenu::addItem(new ZMAdminMenuItem(null, 'config', _zm('Configuration'), null));
    $configGroups = ZMConfig::instance()->getConfigGroups();
    foreach ($configGroups as $group) {
        if ($group->isVisible()) {
            $id = strtolower($group->getName());
            $id = str_replace(' ', '', $id);
            $id = str_replace('/', '-', $id);
            ZMAdminMenu::addItem(new ZMAdminMenuItem('config', $id, _zm($group->getName()), 'configuration.php?gID='.$group->getId()));
        }
    }

    //ZMAdminMenu::buildMenu();

?>

<div id="header">
  <div id="logo">
    <img src="<?php echo $this->asUrl('images/logo-235x64.png', ZMView::RESOURCE) ?>" alt="logo">
  </div>
  <?php if ($request->getUser()) { ?>
    <div id="top-opts">
      <p>
        <?php $userLink = '<a href="'.$admin2->url('update_user').'" onclick="zenmagick.ajaxFormDialog(this.href, \''._zm('User Profile').'\', \'updateUser\'); return false;">'.$request->getUser()->getName().'</a>'; ?>
        <?php _vzm('Logged in as %s', $userLink) ?>
        | <?php echo date('l, F d, Y') ?>
        | <a href="<?php echo $admin2->url('logoff') ?>"><?php _vzm('Log Out') ?></a>
      </p>
      <p>
        <a href="<?php echo ZMSettings::get('apps.store.baseUrl') ?>" target="_blank">Storefront</a>
        | <a href="<?php echo $admin2->url('about') ?>" onclick="zenmagick.ajaxDialog(this.href, 'About ZenMagick', '85%'); return false;"><?php _vzm('About') ?></a>
        | <a href="http://forum.zenmagick.org/" target="_blank"><?php _vzm('Get Help') ?></a>
        | <a href="<?php echo  ZMSettings::get('apps.store.oldAdminUrl') ?>">OLD Admin</a>
      </p>
    </div>
  <?php } ?>
  <p id="main-menu">
    <?php if ($request->getUser()) { ?>
      <a href="<?php echo $admin2->url('index') ?>">Dashboard</a>
      | <a href="<?php echo $admin2->url('catalog_manager') ?>">Catalog Manager</a>
      | <a href="<?php echo $admin2->url('installation') ?>">Installation</a>
      | <a href="<?php echo $admin2->url('plugins') ?>">Plugin Manager</a>
      | <a href="<?php echo $admin2->url('cache_admin') ?>">Cache Admin</a>
      | <a href="<?php echo $admin2->url('ezpages') ?>">EZPages Editor</a>
      | <a href="<?php echo $admin2->url('static_page_editor') ?>">Static Page Editor</a>
      | <a href="<?php echo $admin2->url('admin_users') ?>">Manage Admin Users</a>
      | <a href="<?php echo $admin2->url('l10n') ?>">Translation Helper</a>
      | <a href="<?php echo $admin2->url('console') ?>">Console</a>
    <?php } ?>
  </p>
</div>