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

class Diib_Admin
{
    private static $instance;

    private function __construct() {
        add_action( 'admin_notices', array( $this, 'add_plugin_admin_notices' ) );
        add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
        add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget' ) );
    }

    public static function get_instance() {
        if( null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function add_plugin_admin_menu() {
        require_once 'includes/class-diib-options-page.php';
        $this->_options_page = new Diib_Options_Page();
    }

    public function add_plugin_admin_notices() {
        if( get_current_screen()->id == 'settings_page_diib-settings-oauth' ) {
            return;
        }

        if( !Diib_Oauth::has_access_token() ) {
            include DIIB_PLUGIN_PATH . '/admin/views/not-authorized-notice-view.php';
        }
        else if( !Diib_OAuth::is_access_token_valid() ) {
            include DIIB_PLUGIN_PATH . '/admin/views/invalid-access-token-notice-view.php';
        }
    }

    public function add_dashboard_widget() {
        wp_add_dashboard_widget( 'diib_health_score', __( 'Health Score by diib', 'diib' ), array( $this, 'diib_health_score_widget' ) );
    }

    public function diib_health_score_widget() {
        if( !Diib_Utils::is_website_configured() ) {
            return;
        }

        if( !Diib_Utils::is_authorized() ) {
            return;
        }

        $score = Diib_Utils::get_score( 'yesterday' );
?>
<div class="diib-radial-progress large progress-<?php echo min( $score, 100 ) ?>">
    <div class="circle">
        <div class="mask full">
            <div class="fill"></div>
        </div>
        <div class="mask half">
            <div class="fill"></div>
            <div class="fill fix"></div>
        </div>
    </div>
    <div class="inset"><h1 class="center light"><?php echo $score ?><span>%</span></h1></div>
</div>
<p align="center"><a target="_blank" class="button button-primary" href="<?php echo DIIB_ENDPOINT ?>/login"><?php _e( 'Open Dashboard', 'diib' ) ?></a></p>
<?php
    }
}