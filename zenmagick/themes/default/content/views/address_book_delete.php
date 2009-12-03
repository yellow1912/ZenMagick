<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 *
 * $Id$
 */
?>

<p><?php zm_l10n("Are you sure you would like to delete the selected address from your address book?") ?></p>
<?php $form->open('address_book_delete', '', true, array('onsubmit'=>null)) ?>
    <fieldset>
        <legend><?php zm_l10n("Selected Address") ?></legend>
        <div class="btn">
            <input type="hidden" name="id" value="<?php echo $address->getId() ?>" />
            <input type="submit" class="btn" value="<?php zm_l10n("Delete") ?>" />
        </div>
        <?php $macro->formatAddress($address) ?>
    </fieldset>
</form>
