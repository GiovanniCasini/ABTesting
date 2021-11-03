<?php
/**
 * Plugin Name:       ABtesting
 * Plugin URI:        https://localhost/wp-admin/plugins.php
 * Description:       Plugin per Wordpress che consente di fare A/B testing randomizzando gli utenti su versioni diverse del sito
 * Version:           1.0
 * Requires PHP:      7.2
 * Author:            Giovanni Casini & Cosimo Tanganelli
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path:       /languages
 */ 

function demo_settings()
{
    add_settings_section("section", "Temi da alternare", null, "demo");
    add_settings_field("theme1", "Tema 1", "theme_1_select", "demo", "section");
    add_settings_field("theme2", "Tema 2", "theme_2_select", "demo", "section");  
    register_setting("section", "theme1");
    register_setting("section", "theme2");
}

function theme_1_select()
{
    $themes = wp_get_themes();
    if (count($themes) > 1) {
        $theme_names = array_keys($themes);
    }
    $html = '';
    foreach($theme_names as $theme){
        $html .= "<option value='" . $theme . "'" . selected(get_option('theme1'), $theme) . ">" . $theme . "</option>";
    }
   ?>
        <select name="theme1">
        <?php echo $html; ?>
        </select>
   <?php
}

function theme_2_select()
{
    $themes = wp_get_themes();
    if (count($themes) > 1) {
        $theme_names = array_keys($themes);
    }
    $html = '';
    foreach($theme_names as $theme){
        $html .= "<option value='" . $theme . "'" . selected(get_option('theme2'), $theme) . ">" . $theme . "</option>";
    }
   ?>
        <select name="theme2">
          <?php echo $html; ?>
        </select>
   <?php
}

add_action("admin_init", "demo_settings");

function demo_page()
{
  ?>
      <div class="wrap">
         <h1>ABTesting Settings</h1>
 
         <form method="post" action="options.php">
            <?php
               settings_fields("section");
 
               do_settings_sections("demo");
                 
               submit_button();
            ?>
         </form>
      </div>
   <?php
}

function menu_item()
{
  add_submenu_page("options-general.php", "ABTesting Settings", "ABtesting", "manage_options", "demo", "demo_page");
}
 
add_action("admin_menu", "menu_item");

