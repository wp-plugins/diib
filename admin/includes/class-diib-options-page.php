<?php

/**
 * This file is part of Diib WordPress Plugin.
 *
 *  (c) Diib Inc. <feedback@diib.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

class Diib_Options_Page
{
    public function __construct() {
        add_options_page( 'diib Settings', 'diib', 'manage_options', 'diib-settings-oauth', array( $this, 'auth_page' ) );
        add_submenu_page( null, 'diib Settings - Website Settings', 'Website Settings', 'manage_options', 'diib-settings-website', array( $this, 'website_page' ) );
        add_submenu_page( null, 'diib Settings - Tracking Code Integration', 'Tracking Code Integration', 'manage_options', 'diib-settings-integration', array( $this, 'integration_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    public function auth_page() {
        include DIIB_PLUGIN_PATH . '/admin/views/options-page-auth-view.php';
    }

    public function website_page() {
        $websites = Diib_Unirest::jsonify( Diib_Unirest::get( '/sites' ) );

        if( $websites === null ) {
            $websites = array();
        }

        $diib_website_id = (int)get_option( 'diib_website_id' );

        include DIIB_PLUGIN_PATH . '/admin/views/options-page-website-view.php';
    }

    public function integration_page() {
        $diib_tracking_enabled = get_option( 'diib_tracking_enabled' );
        $diib_track_ecommerce = get_option( 'diib_track_ecommerce' );
        $woocommerce = Diib_Utils::is_woocommerce_installed();
        $tracking_code = Diib_Utils::get_tracking_code( isset( $_GET['flush_transient'] ) );
        $tracker_type = Diib_Utils::get_tracker_type( isset( $_GET['flush_transient'] ) );

        include DIIB_PLUGIN_PATH . '/admin/views/options-page-integration-view.php';
    }

    public function register_settings() {
        register_setting( 'diib-oauth', 'diib_access_token' );
        register_setting( 'diib-oauth', 'diib_refresh_token' );
        register_setting( 'diib-oauth', 'diib_access_token_expiration' );

        register_setting( 'diib-website', 'diib_website_id' );

        register_setting( 'diib-tracking', 'diib_tracking_enabled' );
        register_setting( 'diib-tracking', 'diib_track_ecommerce' );
    }

    public function enqueue_scripts($hook) {
        if( $hook == 'settings_page_diib-settings-oauth' ) {
            wp_enqueue_script( 'diib-options-page', DIIB_PLUGIN_URL . '/admin/js/options-page.min.js', array( 'jquery' ) );
        }

        wp_enqueue_style( 'diib-options-page', DIIB_PLUGIN_URL . '/admin/css/options-page.min.css' );
    }
}