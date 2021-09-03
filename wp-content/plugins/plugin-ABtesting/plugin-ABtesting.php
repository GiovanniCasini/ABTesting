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

class WPD_Theme_Switcher {

    private $themes = array(
        'twentynineteen',
        'twentytwentyone'
    );
    private $current_theme = '';
    private $cookie = 'wpd_theme_switcher_cookie';

    function __construct() {

        if( empty( $this->current_theme ) && !isset( $_COOKIE[ $this->cookie ] ) ) {
            $this->current_theme = $this->themes[ array_rand( $this->themes ) ];
            setcookie( $this->cookie, $this->current_theme, time() + (10 * 365 * 24 * 60 * 60) );
        } else {
            $this->current_theme = $_COOKIE[ $this->cookie ];
        }

        // don't switch themes for admin requests
        if( ! is_admin() ){
            add_filter( 'template', array( $this, 'theme_switcher' ) );
            add_filter( 'option_template', array( $this, 'theme_switcher' ) );
            add_filter( 'option_stylesheet', array( $this, 'theme_switcher' ) );
        }

    }

    function theme_switcher(){
        return $this->current_theme;
    }

}
new WPD_Theme_Switcher();

?>