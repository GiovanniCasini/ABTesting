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

$set_theme = set_random_theme();

function set_random_theme() {
        $random_theme = '';
        $themes = wp_get_themes();
        if ( 1 < count($themes) ) {
                $theme_names = array_keys($themes);
                $length = count($theme_names);
                $selected = rand(0,$length-1);
                $random_theme = $theme_names[$selected];
        }
        return $random_theme;
}

function set_theme_template($template) {
    global $set_theme;

        $theme = $set_theme;
                
    if (empty($theme)) {
        return $template;
    }

    $theme = wp_get_theme($theme);

    if (empty($theme)) {
                
        return $template;
    }
        
    return $theme['Template'];
}

function set_theme_stylesheet($stylesheet) {
    global $set_theme;

        $theme = $set_theme;

    if (empty($theme)) {
        return $stylesheet;
    }

    $theme = wp_get_theme($theme);

    if (empty($theme)) {
        return $stylesheet;
    }

    return $theme['Stylesheet'];
}

if ( !is_admin() ) {
        add_filter('stylesheet', 'set_theme_stylesheet');
        add_filter('template', 'set_theme_template');
}
?>