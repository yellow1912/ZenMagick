<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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


/**
 * Messages to be displayed to the user.
 *
 * <p>Messages will be saved in the session if not delivered.</p>
 *
 * <p>Code supported message levels are:</p>
 * <ul>
 *  <li><code>error</code></li>
 *  <li><code>warn</code></li>
 *  <li><code>success</code></li>
 *  <li><code>msg</code> (this is the default if no type specified)</li>
 * </ul>
 *
 * @author mano
 * @package org.zenmagick.service
 * @version $Id$
 */
class ZMMessages extends ZMObject {
    var $messages_;
    var $uniqueMsgRef_;


    /**
     * Create new instance.
     */
    function __construct() {
    global $messageStack;

        parent::__construct();

        $this->messages_ = array();
        $this->uniqueMsgRef_ = array();
        $this->_loadMessageStack();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return parent::instance('Messages');
    }


    /**
     * Load messages from zen-cart message stack.
     */
    function _loadMessageStack() {
    global $messageStack;

        $session = ZMRequest::getSession();

        // add messages generated by zen-cart so far
        if (isset($messageStack) && isset($messageStack->messages)) {
            foreach ($messageStack->messages as $zenMessage) {
                $pos = strpos($zenMessage['text'], "/>");
                $text = substr($zenMessage['text'], $pos+2);
                $this->add($text, 
                  (false === strpos($zenMessage['params'], 'Error') 
                    ? (false === strpos($zenMessage['params'], 'Success') ? "warn" : "msg") : "error"));
            }
        } else {
            // look for session messages
            $this->addAll($session->getMessages());
        }

        // also check for messages in the request...
        if (null != ($error = ZMRequest::getParameter('error_message'))) {
            $this->error($error);
        }
        if (null != ($error = ZMRequest::getParameter('credit_class_error'))) {
            $this->error($error);
        }
        if (null != ($info = ZMRequest::getParameter('info_message'))) {
            $this->info($info);
        }

        $session->clearMessages();
    }

    /**
     * Generic add a message.
     *
     * @param string text The message text.
     * @param string type The message type; default is 'msg'.
     * @param string ref The referencing resource; default is <code>global</code>.
     */
    function add($text, $type='msg', $ref='global') {
        if (array_key_exists($text, $this->uniqueMsgRef_))
            return;

        $this->uniqueMsgRef_[$text] = $text;
        array_push($this->messages_, ZMLoader::make("Message", $text, $type, $ref));
    }

    /**
     * Add an error message.
     *
     * @param string text The message text.
     * @param string ref The referencing resource; default is <code>global</code>.
     */
    function error($text, $ref='global') {
        $this->add($text, 'error', $ref);
    }

    /**
     * Add a warning message.
     *
     * @param string text The message text.
     * @param string ref The referencing resource; default is <code>global</code>.
     */
    function warn($text, $ref='global') {
        $this->add($text, 'warn', $ref);
    }

    /**
     * Add a default message.
     *
     * @param string text The message text.
     * @param string ref The referencing resource; default is <code>global</code>.
     */
    function msg($text, $ref='global') {
        $this->add($text, 'msg', $ref);
    }

    /**
     * Add a success message.
     *
     * @param string text The message text.
     * @param string ref The referencing resource; default is <code>global</code>.
     */
    function success($text, $ref='global') {
        $this->add($text, 'success', $ref);
    }

    /**
     * Add a group of messages.
     *
     * @param array messages List of <code>ZMMessage</code> instances.
     */
    function addAll($messages) {
        foreach ($messages as $msg) {
            $this->add($msg->getText(), $msg->getType(), $msg->getRef());
        }
    }

    /**
     * Checks if there are any messages available.
     *
     * @param string ref The referencing resource; default is <code>null</code> for all.
     * @return boolean <code>true</code> if messages are available, <code>false</code> if not.
     */
    function hasMessages($ref=null) {
        if (null === $ref) {
            return 0 != count($this->messages_);
        }

        foreach ($this->messages_ as $message) {
            if ($ref == $message->ref_) {
                return true;
              }
        }

        return false;
    }

    /**
     * Get all messages.
     *
     * @param string ref The referring resource; default is <code>null</code> for all.
     * @return array List of <code>ZMMessage</code> instances.
     */
    function getMessages($ref=null) {
        if (null === $ref) {
            return $this->messages_;
        }

        $messages = array();
        foreach ($this->messages_ as $ii => $msg) {
            if ($ref == $msg->ref_) {
                array_push($messages, $msg);
            }
        }

        return $messages;
    }

}

?>
