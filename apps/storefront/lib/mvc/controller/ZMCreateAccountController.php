<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\Runtime;
use zenmagick\base\events\Event;

/**
 * Request controller for account creation page.
 *
 * <p>The <em>createDefaultAddress</em> property can be used to control whether or not
 * to create a default address entry in the address book. Obviously, the validation rules
 * for the registration form need to be adjusted accordingly.</p>
 *
 * <p>The property may be set by specifying a controllerDefinition value in the <em>URL mapping</em>
 * like this:</p>
 * <p><code>ZMUrlManager::instance()->setMapping('create_account', array('controller' => 'CreateAccountController#createDefaultAddress=false'), false);</code></p>
 *
 * @author DerManoMann
 * @package zenmagick.store.sf.mvc.controller
 */
class ZMCreateAccountController extends ZMController {
    private $createDefaultAddress_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->createDefaultAddress_ = true;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Set create default address flag.
     *
     * @param boolean value The new value.
     */
    public function setCreateDefaultAddress($value) {
        // make sure we convert to boolean; typically this would be set via a bean definition
        $this->createDefaultAddress_ = ZMLangUtils::asBoolean($value);
        ZMLogging::instance()->log('createDefaultAddress set to: '.$this->createDefaultAddress_, ZMLogging::TRACE);
    }

    /**
     * {@inheritDoc}
     */
    public function preProcess($request) {
        $request->getToolbox()->crumbtrail->addCrumb("Account", $request->url('account', '', true));
        $request->getToolbox()->crumbtrail->addCrumb($request->getToolbox()->utils->getTitle());
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $registration = $this->getFormData($request);

        $clearPassword = $registration->getPassword();
        $account = $registration->getAccount();
        $account->setPassword($this->container->get('authenticationManager')->encryptPassword($clearPassword));
        $account = $this->container->get('accountService')->createAccount($account);

        $address = null;
        $addressService = $this->container->get('addressService');
        if ($this->createDefaultAddress_) {
            // account and address refer to each other...
            $address = $registration->getAddress();
            $address->setPrimary(true);
            $address->setAccountId($account->getId());
            $address = $addressService->createAddress($address);
            $account->setDefaultAddressId($address->getId());
            $this->container->get('accountService')->updateAccount($account);
        }

        // here we have a proper account, so time to let other know about it
        $args = array('request' => $request, 'controller' => $this, 'account' => $account, 'address' => $address, 'clearPassword' => $clearPassword);
        Runtime::getEventDispatcher()->dispatch('create_account', new Event($this, $args));

        // in case it got changed
        $this->container->get('accountService')->updateAccount($account);
        if (null != $address) {
            $addressService->updateAddress($address);
        }

        $session = $request->getSession();
        $session->regenerate();
        $session->setAccount($account);
        $session->restoreCart();

        $discountCoupon = null;
        if (null != ($newAccountDiscountCouponId = Runtime::getSettings()->get('zenmagick.apps.store.newAccountDiscountCouponId'))) {
            $discountCoupon = ZMCoupons::instance()->getCouponForId($newAccountDiscountCouponId, $session->getLanguageId());
        }
        $newAccountGVAmountCoupon = null;
        if (null != ($newAccountGVAmount = Runtime::getSettings()->get('zenmagick.apps.store.newAccountGVAmount'))) {
            // set up coupon
            $couponCode = ZMCoupons::instance()->createCouponCode($account->getEmail());
            $coupon = ZMCoupons::instance()->createCoupon($couponCode, $newAccountGVAmount, ZMCoupons::TYPPE_GV);
            // the receiver of the gv
            $gvReceiver = $this->container->get('ZMGVReceiver');
            $gvReceiver->setEmail($account->getEmail());
            // the sender
            $senderAccount = $this->container->get('ZMAccount');
            $senderAccount->setFirstName(Runtime::getSettings('storeName'));
            ZMCoupons::instance()->createCouponTracker($coupon, $senderAccount, $gvReceiver);
            $newAccountGVAmountCoupon = $coupon;
        }

        // account email
        $context = array(
            'currentAccount' => $account,
            'office_only_html' => '', 'office_only_text' => '',
            'newAccountDiscountCoupon' => $discountCoupon,
            'newAccountGVAmountCoupon' => $newAccountGVAmountCoupon
        );

        $message = $this->container->get('messageBuilder')->createMessage('welcome', $account->isHtmlEmail(), $request, $context);
        $message->setSubject(sprintf(_zm("Welcome to %s"), ZMSettings::get('storeName')))->setTo($account->getEmail(), $account->getFullName())->setFrom(ZMSettings::get('storeEmail'));
        $this->container->get('mailer')->send($message);

        if (ZMSettings::get('isEmailAdminCreateAccount')) {
            // store copy
            $context = $request->getToolbox()->macro->officeOnlyEmailFooter($account->getFullName(), $account->getEmail(), $session);
            $context['currentAccount'] = $account;

            $message = $this->container->get('messageBuilder')->createMessage('welcome', ZMSettings::get('isEmailAdminExtraHtml', false), $request, $context);
            $message->setSubject(sprintf(_zm("[CREATE ACCOUNT] Welcome to %s"), ZMSettings::get('storeName')))->setTo(ZMSettings::get('emailAdminCreateAccount'))->setFrom(ZMSettings::get('storeEmail'));
            $this->container->get('mailer')->send($message);
        }

        $this->messageService->success(_zm("Thank you for signing up"));

        $stickyUrl = $request->getFollowUpUrl();
        return $this->findView('success', array('currentAccount' => $account), array('url' => $stickyUrl));
    }

}