<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

$resourceManager->jsFile('lightbox/prototype.js', $resourceManager::FOOTER) ?>
<?php $resourceManager->jsFile('lightbox/scriptaculous.js?load=effects', $resourceManager::FOOTER) ?>
<?php $resourceManager->jsFile('lightbox/lightbox.js', $resourceManager::FOOTER) ?>

<?php $crumbtrail->addCategoryPath($request->getCategoryPathArray())->addManufacturer($request->getManufacturerId())->addProduct($currentProduct->getId()) ?>

<?php $manufacturer = $currentProduct->getManufacturer() ?>
<h2><?php echo $html->encode(null != $manufacturer ? $manufacturer->getName() : '') ?> <?php echo $html->encode($currentProduct->getName()) ?></h2>

<?php echo $form->addProduct($currentProduct->getId()) ?>
  <?php $imageInfo = $currentProduct->getImageInfo() ?>
  <div>
      <?php if ($imageInfo->hasLargeImage()) { ?>
          <a href="<?php echo $request->absoluteUrl($imageInfo->getLargeImage()) ?>" rel="lightbox" title="<?php echo $html->encode($currentProduct->getName()) ?>"><?php echo $html->image($imageInfo, ZMProducts::IMAGE_MEDIUM) ?></a>
      <?php } else { ?>
          <?php echo $html->image($imageInfo, ZMProducts::IMAGE_MEDIUM) ?>
      <?php } ?>
      <div id="desc"><?php echo $currentProduct->getDescription() ?></div>
      <?php if (null != $manufacturer) { ?>
        <?php _vzm("Producer") ?>: <?php echo $html->encode($manufacturer->getName()); ?><br />
      <?php } ?>
      <p id="price"><?php echo $html->encode($currentProduct->getModel()) ?>: <?php echo $macro->productPrice($currentProduct) ?></p>
  </div>

  <?php $attributes = $macro->productAttributes($currentProduct); ?>
  <?php foreach ($attributes as $attribute) { ?>
      <fieldset>
          <legend><?php echo $html->encode($attribute['name']) ?></legend>
          <?php foreach ($attribute['html'] as $option) { ?>
            <p><?php echo $option ?></p>
          <?php } ?>
      </fieldset>
  <?php } ?>

  <fieldset>
      <legend><?php _vzm("Shopping Options") ?></legend>
      <?php $minMsg = ""; if (1 < $currentProduct->getMinOrderQty()) { $minMsg = sprintf(_zm(" (Order minimum: %s)"), $currentProduct->getMinOrderQty()); } ?>
      <label for="cart_quantity"><?php _vzm("Quantity") ?><?php echo $minMsg; ?></label>
      <input type="text" id="cart_quantity" name="cart_quantity" value="1" maxlength="6" size="4" />
      <input type="submit" class="btn" value="<?php _vzm("Add to cart") ?>" />
  </fieldset>

  <?php $addImgList = $currentProduct->getAdditionalImages(); ?>
  <?php if (0 < count($addImgList)) { ?>
      <fieldset>
          <legend><?php _vzm("Additional Images") ?></legend>
          <?php foreach ($addImgList as $addImg) { ?>
              <?php if ($addImg->hasLargeImage()) { ?>
                  <a href="<?php echo $request->absoluteUrl($addImg->getLargeImage()) ?>" onclick="productPopup(event, this); return false;"><img src="<?php echo $request->absoluteUrl($addImg->getDefaultImage()) ?>" alt="" title="" /></a>
              <?php } else { ?>
                  <img src="<?php echo $request->absoluteUrl($addImg->getDefaultImage()) ?>" alt="" title="" />
              <?php } ?>
          <?php } ?>
      </fieldset>
  <?php } ?>
  <?php if ($currentProduct->hasReviews() || $currentProduct->getTypeSetting('reviews') || $currentProduct->getTypeSetting('tell_a_friend')) { ?>
      <fieldset>
          <legend><?php _vzm("Other Options") ?></legend>
          <?php if ($currentProduct->hasReviews()) { ?>
              <a class="btn" href="<?php echo $net->url('product_reviews', '') ?>"><?php _vzm("Read Reviews") ?></a>
          <?php } ?>
          <?php if ($currentProduct->getTypeSetting('reviews')) { ?>
              <a class="btn" href="<?php echo $net->url('product_reviews_write', '') ?>"><?php _vzm("Write a Review") ?></a>
          <?php } ?>
          <?php if ($currentProduct->getTypeSetting('tell_a_friend')) { ?>
              <a class="btn" href="<?php echo $net->url('tell_a_friend', "products_id=".$currentProduct->getId()) ?>"><?php _vzm("Tell a friend about this product") ?></a>
          <?php } ?>
      </fieldset>
  <?php } ?>
</form>

<?php $similarOrder = $currentProduct->getProductAssociations('similarOrder', array('limit' => 3)); if (0 < count($similarOrder)) { ?>
  <h3>Customers that bought this product also bought:</h3>
  <?php foreach ($similarOrder as $assoc) { $aproduct = $container->get('productService')->getProductForId($assoc->getProductId(), $session->getLanguageId()); ?>
  <p><?php echo $aproduct->getName() ?></p>
  <?php } ?>
<?php } ?>
