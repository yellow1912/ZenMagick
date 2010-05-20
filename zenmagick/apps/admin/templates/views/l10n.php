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
 *
 * $Id$
 */
?>
<?php  

  $themePath = '';
  $defaults = isset($_POST['defaults']) || isset($_GET['defaults']);
  $merge = isset($_POST['merge']) || isset($_GET['merge']);
  if (isset($_POST['theme'])) {
      $themePath = $_POST['theme'];
  }

  if (isset($_GET['theme']) && isset($_GET['download'])) {
      header('Content-Type: text/PHP');
      header('Content-Disposition: attachment; filename=l10n.php;');

      $map = zm_build_theme_l10n_map(DIR_FS_CATALOG.ZM_ROOT."themes/" . $_GET['theme'], $defaults, $merge);
      echo "<?php\n\n    /*\n     * Language mapping generated by ZenMagick Admin v" . ZMSettings::get('zenmagick.version') . "\n     */\n";
      echo "\n";
      echo '    zm_l10n_add(array('."\n";
      $komma = false;
      $firstfile = true;
      $globalMap = array();
      foreach ($map as $file => $strings) {
          if (null === $strings) {
              continue;
          }
          if (!$firstfile) echo ",\n\n";
          echo "        // " . $file . "\n";
          $nextfile = true;
          foreach ($strings as $key => $value) {
              if ($komma && !$nextfile) echo ",\n";
              $quote = '"';
              // either we have escaped single quotes or double quotes that are not escaped
              if (false !== strpos($key, '\\\'') || (false !== strpos($key, '"') && false === strpos($key, '\\"'))) { $quote = "'"; }
              if (isset($globalMap[$key])) {
                  // key exists!
                  if ($globalMap[$key] != $value) {
                      // same key different value!
                      echo '        ' . '/*** WARNING: mapping mismatch ***/';
                  }
                  echo '        ' . '/* DUPLICATE */ //' . $quote . $key . $quote . ' => ' . $quote . $value . $quote;
              } else {
                  echo '        ' . $quote . $key . $quote . ' => ' . $quote . $value . $quote;
                  $komma = true;
                  $globalMap[$key] = $value;
              }
              $nextfile = false;
          }
          $firstfile = false;
      }
      echo "\n    ));\n?>";
      return;
  }

?>

<h2>ZenMagick Language Tool</h2>
<p>This tool helps you find language strings in your themes. Just select a theme and you will 
  get a full list of all strings and where they are used.</p>
<p>The selected mapping can also be downloaded in a format that you can cut'n paste right into your <code>l10n.php</code> file.</p>
<p>Inherited mappings are mappings defined in <code>l10n.php</code> files in themes further up the theme chain.</p>
<p><strong>NOTE:</strong> '%s' and other strings starting with '%' are used as placeholders for things like order numbers, etc.</p>

<form action="<?php echo $toolbox->admin->url() ?>" method="POST">
  <fieldset>
    <legend>Select Theme to display the language mappings</legend>
    <select id="theme" name="theme" onchange="this.form.submit()">
      <option value="">Select Theme</option>
        <?php foreach (ZMThemes::instance()->getThemes() as $theme) { ?>
        <?php $selected = $themePath == $theme->getThemeId() ? ' selected="selected"' : ''; ?>
        <option value="<?php echo $theme->getThemeId(); ?>"<?php echo $selected ?>><?php echo $theme->getName(); ?></option>
      <?php } ?>
    </select>
    <br>
    <input type="checkbox" id="defaults" name="defaults" value="true"<?php echo ($defaults?' checked="checked"':'')?>><label for="defaults">Include default theme strings</label><br>
    <input type="checkbox" id="merge" name="merge" value="true"<?php echo ($merge?' checked="checked"':'')?>><label for="merge">Merge with existing mappings</label><br>
    <input type="submit" value="Display Mapping">
  </fieldset>
</form>
<?php if ('' != $themePath) { ?>
  <a href="<?php echo $toolbox->admin->url(null, 'theme='.$themePath.'&download=full'.($merge?"&amp;merge=true":"")) ?>">Download mapping</a>
  <?php $map = zm_build_theme_l10n_map(DIR_FS_CATALOG.ZM_ROOT."themes/" . $themePath, $defaults, $merge) ?>
  <?php foreach ($map as $file => $strings) { ?>
    <h3><?php echo $file ?></h3>
    <?php foreach ($strings as $key => $value) { ?>
      &nbsp;&nbsp;'<?php echo $key ?>' =&gt; '<?php echo $value ?>';<br>
    <?php } ?>
  <?php } ?>
<?php } ?>
