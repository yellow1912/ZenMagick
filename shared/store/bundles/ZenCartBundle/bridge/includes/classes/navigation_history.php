<?php
/**
 * Navigation_history Class.
 *
 * @package classes
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: navigation_history.php 4383 2006-09-04 00:42:07Z drbyte $
 */
use zenmagick\base\Runtime;
/**
 * Navigation_history Class.
 * This class is used to manage navigation snapshots
 *
 * It has been modified to work inside of Zenmagick.
 *
 * Modifications:
 * use __construct method
 * use ZenMagick request service instead of _GET/_POST
 * ignores XMLHttpRequest requests
 *
 *
 * @package classes
 */
class navigationHistory {
    public $snapshot;
    public $path;
    private $pos;

    function __construct() {  $this->reset(); }
    function reset() { $this->path = $this->snapshot = array(); }
    function clear_snapshot() { $this->snapshot = array(); }
    function pos() { return count($this->path) - 1; }
    function is_current_page($pos) {
        return $this->path[$pos]['page'] == Runtime::getContainer()->get('request')->getRequestId();
    }

    function add_current_page() {
        $request = \zenmagick\base\Runtime::getContainer()->get('request');
        if ($request->isAjax()) return;

        $cPath = $request->getCategoryPath();

        $set = true;
        for ($i=0, $n=sizeof($this->path); $i<$n; $i++) {
            if ($this->is_current_page($i)) continue;

            if (null == $cPath) {
                array_splice($this->path, $i);
                $set = true;
                break;
            } else {
                if (!isset($this->path[$i]['get']['cPath'])) continue;

                if ($this->path[$i]['get']['cPath'] == $cPath) {
                    array_splice($this->path, ($i+1));
                    $set = false;
                    break;
                } else {
                    $old_cPath = explode('_', $this->path[$i]['get']['cPath']);
                    $new_cPath = explode('_', $cPath);

                    $exit_loop = false;
                    for ($j=0, $n2=sizeof($old_cPath); $j<$n2; $j++) {
                        if ($old_cPath[$j] != $new_cPath[$j]) {
                            array_splice($this->path, ($i));
                            $set = true;
                            $exit_loop = true;
                            break;
                        }
                    }
                    if ($exit_loop) break;
                }
            }
        }

        if ($set) {
            $get_vars = $request->getParameterMap();
            unset($get_vars[$request->getRequestIdKey()]);

            $this->path[] = array('page' => $request->getParameter('main_page', 'index'),
                                  'mode' => $request->isSecure() ? 'SSL' : 'NONSSL',
                                  'get' => $get_vars,
                                  'post' => array());
        }
    }

    function remove_current_page() {
        $pos = $this->pos();
        if ($this->is_current_page($pos)) unset($this->path[$pos]);
    }

    function set_snapshot($page = null) {
        if (is_array($page)) {
            $this->snapshot = $page;
        } else {
            $request = zenmagick\base\Runtime::getContainer()->get('request');
            $snap = array();
            $snap['get'] = $request->getParameterMap();
            unset($snap['get'][$request->getRequestIdKey()]);
            $snap['mode'] = $request->isSecure() ? 'SSL' : 'NONSSL';
            $snap['page'] = $request->getParameter('main_page', 'index');
            $snap['post'] = array();
            $this->snapshot = $snap;
        }
    }

    function set_path_as_snapshot($history = 0) {
        $this->snapshot = $this->path[$this->pos() - $history];
    }
}
