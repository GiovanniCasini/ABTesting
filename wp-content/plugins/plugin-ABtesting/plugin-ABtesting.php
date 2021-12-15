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

function demo_settings()     // definisce impostazioni del plugin
{
    add_settings_section("section", "Temi da alternare", null, "demo");
    add_settings_field("theme1", "Tema 1", "theme_1_select", "demo", "section");
    add_settings_field("theme2", "Tema 2", "theme_2_select", "demo", "section");  
    add_settings_section("urlsection", "URL", null, "demo");
    add_settings_field("goalurl", "URL Traguardo", "urlTraguardo", "demo", "urlsection");
    register_setting("section", "theme1");
    register_setting("section", "theme2");
    register_setting("urlsection", "goalurl");
}

function theme_1_select()  // seleziona primo tema
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

function theme_2_select()   // seleziona secondo tema
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

function urlTraguardo(){   // seleziona URL di arrivo per calcolo statistiche
    $html = "<input type='text' value='" . get_option('goalurl') . "' name='goalurl'></input>";
    echo $html;
}

add_action("admin_init", "demo_settings");

function demo_page()   // crea pagina impostazioni del plugin
{
  ?>
      <div class="wrap">
         <h1>ABTesting Settings</h1>
 
         <form method="post" action="options.php">
            <?php
               settings_fields("section");
               settings_fields("urlsection");
 
               do_settings_sections("demo");
                 
               submit_button();
            ?>
         </form>
      </div>
   <?php
}

function menu_item()  // crea submenu del plugin
{
  add_submenu_page("options-general.php", "ABTesting Settings", "ABtesting", "manage_options", "demo", "demo_page");
}
 
add_action("admin_menu", "menu_item");

function get_current_url(){
    if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')$url = "https://";   
        else $url = "http://";
        $url.= $_SERVER['HTTP_HOST'];
        $url.= $_SERVER['REQUEST_URI'];
    return $url;
}

function publish($sql){
    $servername = "localhost";
            $username = "root";
            $password = "root";
            $dbname = "abtesting";
            // Create connection
            $conn = mysqli_connect($servername, $username, $password, $dbname);
            // Check connection
            if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
            }
    
            if (!mysqli_query($conn, $sql)) {
                echo "Error: " . $sql . "<br>" . mysqli_error($conn);
            }
            mysqli_close($conn);
}

function cookie_manager() {   // gestisce cookie per tenere traccia dati utente
    $themes_array = [get_option('theme1'), get_option('theme2')];
    $rand_index = array_rand($themes_array);
    $user_theme = $themes_array[$rand_index];

    if(!isset($_COOKIE['userid'])) {
        setcookie('userid', uniqid('', true), 0, '/');
        setcookie('theme', $user_theme, 0, '/');
        setcookie('currenturl', get_current_url(), 0, '/');
        setcookie('time_started', time(), 0, '/');
        switch_theme($user_theme);
    }
    else{
        if(get_current_url() != $_COOKIE['currenturl'] && $_COOKIE["goal"] != "true"){
            if(get_current_url() == get_option('goalurl'))
                setcookie('goal', 'true', 0, '/');
            
            echo get_current_url();
            $total_time = time() - $_COOKIE["time_started"];
            setcookie('time_started', time(), 0, '/');
            $sql = "INSERT INTO testingdata (timespent, userid, theme, webpage)
            VALUES ('". $total_time ."', '".  $_COOKIE["userid"] ."', '" . $_COOKIE["theme"] . "','" . $_COOKIE["currenturl"] . "')";
            publish($sql);
            setcookie('currenturl', get_current_url(), 0, '/');
            $total_time = 0;
        }
    }
    
}
   
add_action('init', 'cookie_manager');

