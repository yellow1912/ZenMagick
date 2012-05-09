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
namespace zenmagick\apps\store\storefront\controller;


/**
 * Request controller for checkout shipping page.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class CheckoutConfirmationController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        // some defaults
        $orderFormContent =  '';
        $orderFormUrl = $request->url('checkout_process', '', true);

        $shoppingCart = $request->getShoppingCart();
        if (null != ($paymentType = $shoppingCart->getSelectedPaymentType())) {
            $orderFormContent = $paymentType->getOrderFormContent($request);
            $orderFormUrl = $paymentType->getOrderFormUrl($request);
        }

        return $this->findView(null, array('shoppingCart' => $shoppingCart, 'orderFormContent' => $orderFormContent, 'orderFormUrl' => $orderFormUrl));
    }

    public function processPost($request) {
        $shoppingCart = $request->getShoppingCart();
        $checkoutHelper = $shoppingCart->getCheckoutHelper();
        $settingsService = $this->container->get('settingsService');

        if (!$checkoutHelper->verifyHash($request)) {
            return $this->findView('check_cart');
        }

        if ('free_free' == $_SESSION['shipping']) { // <johnny> When does this actually happen?
            Runtime::getLogging()->warn('fixing free_free shipping method info');
            $_SESSION['shipping'] = array('title' => _zm('Free Shipping'), 'cost' => 0, 'id' => 'free_free');
        }

        if (null !== ($viewId = $checkoutHelper->validateCheckout($request, false))) {
            return $this->findView($viewId);
        }
        if (null !== ($viewId = $checkoutHelper->validateAddresses($request, true))) {
            return $this->findView($viewId);
        }

        if (null != ($comments = $request->getParameter('comments'))) {
            $shoppingCart->setComments($comments);
        }

        if ($settingsService->get('isConditionsMessage') && !Toolbox::asBoolean($request->getParameter('conditions'))) {
            $this->messageService->error(_zm('Please confirm the terms and conditions bound to this order by ticking the box below.'));
            return $this->findView();
        }

        if (null != ($paymentMethod = $request->getParameter('payment'))) {
            $request->getSession()->setValue('payment', $paymentMethod);
        }
        return $this->processGet($request);
    }
}


/*
 * @todo implement all this
 *
iif (!isset($credit_covers)) $credit_covers = FALSE;
if ($credit_covers) {
  unset($_SESSION['payment']);
  $_SESSION['payment'] = '';
}

global $$_SESSION['payment'];
if (is_array($payment_modules->modules)) {
  $payment_modules->pre_confirmation_check();
}
// update customers_referral with $_SESSION['gv_id']
if ($_SESSION['cc_id']) {
  $discount_coupon_query = "SELECT coupon_code
                            FROM " . TABLE_COUPONS . "
                            WHERE coupon_id = :couponID";

  $discount_coupon_query = $db->bindVars($discount_coupon_query, ':couponID', $_SESSION['cc_id'], 'integer');
  $discount_coupon = $db->Execute($discount_coupon_query);

  $customers_referral_query = "SELECT customers_referral
                               FROM " . TABLE_CUSTOMERS . "
                               WHERE customers_id = :customersID";

  $customers_referral_query = $db->bindVars($customers_referral_query, ':customersID', $_SESSION['customer_id'], 'integer');
  $customers_referral = $db->Execute($customers_referral_query);

  // only use discount coupon if set by coupon
  if ($customers_referral->fields['customers_referral'] == '' and CUSTOMERS_REFERRAL_STATUS == 1) {
    $sql = "UPDATE " . TABLE_CUSTOMERS . "
            SET customers_referral = :customersReferral
            WHERE customers_id = :customersID";

    $sql = $db->bindVars($sql, ':customersID', $_SESSION['customer_id'], 'integer');
    $sql = $db->bindVars($sql, ':customersReferral', $discount_coupon->fields['coupon_code'], 'string');
    $db->Execute($sql);
  } else {
    // do not update referral was added before
  }
}

// if shipping-edit button should be overridden, do so
$editShippingButtonLink = zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL');
if (method_exists($$_SESSION['payment'], 'alterShippingEditButton')) {
  $theLink = $$_SESSION['payment']->alterShippingEditButton();
  if ($theLink) $editShippingButtonLink = $theLink;
}
// deal with billing address edit button
$flagDisablePaymentAddressChange = false;
if (isset($$_SESSION['payment']->flagDisablePaymentAddressChange)) {
  $flagDisablePaymentAddressChange = $$_SESSION['payment']->flagDisablePaymentAddressChange;
}
 */
