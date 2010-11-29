<?php
/*
 * ZenMagick - Smart e-commerce
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
<?php $admin2->title() ?>

<table class="grid">
  <tr>
    <th><?php _vzm('Template') ?></th>
    <th><?php _vzm('Text') ?></th>
    <th><?php _vzm('HTML') ?></th>
  </tr>
  <?php foreach ($templateInfo as $name => $formats) { ?>
    <tr>
      <td><?php echo $name ?></td>
      <?php
        $textLink = null;
        if (in_array('text', $formats)) {
            $textLink = '<a target="_blank" href="'.$admin2->url(null, 'template='.$name.'&format=text').'">'._zm('Show').'</a>';
        }
      ?>
      <td><?php echo (null != $textLink ? $textLink : '') ?></td>
      <?php
        $htmlLink = null;
        if (in_array('html', $formats)) {
            $htmlLink = '<a target="_blank" href="'.$admin2->url(null, 'template='.$name.'&format=html').'">'._zm('Show').'</a>';
        }
      ?>
      <td><?php echo (null != $htmlLink ? $htmlLink : '') ?></td>
    </tr>
  <?php } ?>
</table>