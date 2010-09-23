/*
 * ZenMagick - Smart e-commerce
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
 *
 * $Id: zenmagick.js 1966 2009-02-14 10:52:50Z dermanomann $
 */

// confirm user input
function zm_user_confirm(msg) {
    return confirm(msg);
}

// ZenMagick menu
var zm_admin_init = function() {
  if (document.all && document.getElementById) {
    navRoot = document.getElementById("secnav");
    for (ii=0; ii<navRoot.childNodes.length; ++ii) {
      node = navRoot.childNodes[ii];
      if (node.nodeName=="LI") {
        node.onmouseover=function() {
          this.className+=" over";
        }
        node.onmouseout=function() {
          this.className=this.className.replace(" over", "");
        }
      }
    }
  }
}

// zen-cart menu
function zen_admin_init() {
  cssjsmenu('navbar');
  if (document.getElementById) {
    var kill = document.getElementById('hoverJS');
    kill.disabled = true;
  }
}

function init_page() {
    zm_admin_init();
    zen_admin_init();
}

window.onload = init_page;