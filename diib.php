<?php
/**
 * @package   diib
 * @author    diib Inc. <support@diib.com>
 * @license   GPLv2 or later
 * @link      https://connect.diib.com/wordpress
 * @copyright 2015 Diib Inc.
 *
 * @wordpress-plugin
 * Plugin Name:       diib
 * Description:       We turn your complex data into simple answers that help you grow
 * Text Domain:       diib
 * Version:           1.0
 * Author:            Diib Inc.
 * Author URI:        https://www.diib.com
 * License:           GPLv2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 */

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

if( !defined( 'WPINC' ) ) {
    die;
}

if( !defined( 'ENABLE_DIIB_TRACKING_CODE' ) ) {
    define( 'ENABLE_DIIB_TRACKING_CODE', true );
}

if( !defined( 'DIIB_OAUTH2_CLIENT_ID' ) ) {
    define( 'DIIB_OAUTH2_CLIENT_ID', '2_5m9z8z4aoicckwssck4ow00gcgc4ko8ck8wgcs8kokc08wogkw' );
}

if( !defined( 'DIIB_ENDPOINT' ) ) {
    define( 'DIIB_ENDPOINT', 'https://app.diib.com' );
}

if( !defined('DIIB_OAUTH2_ENDPOINT') ) {
    define( 'DIIB_OAUTH2_ENDPOINT', DIIB_ENDPOINT . '/oauth/v2' );
}

define( 'DIIB_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'DIIB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once DIIB_PLUGIN_PATH . '/lib/unirest-php/lib/Unirest.php';
require_once DIIB_PLUGIN_PATH . '/includes/class-diib-utils.php';
require_once DIIB_PLUGIN_PATH . '/includes/class-diib-oauth.php';
require_once DIIB_PLUGIN_PATH . '/includes/class-diib-unirest.php';

if( Diib_OAuth::has_access_token() && !Diib_OAuth::is_access_token_valid() ) {
    Diib_OAuth::refresh_token();
}

if( Diib_Utils::should_track() ) {
    require_once DIIB_PLUGIN_PATH . '/includes/class-diib-trackingcode-integration.php';
    Diib_TrackingCode_Integration::setup_integrators();
    Diib_TrackingCode_Integration::initialize();
}

add_action( 'plugins_loaded', array( 'Diib_Utils', 'load_textdomain' ) );

if( is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) ) {
    require_once DIIB_PLUGIN_PATH . '/admin/class-diib-admin.php';
    add_action( 'plugins_loaded', array( 'Diib_Admin', 'get_instance' ) );
}

function diib_activate() {
    update_option( 'diib_tracking_enabled', true );
    update_option( 'diib_track_ecommerce', true );
}

register_activation_hook( __FILE__, 'diib_activate' );