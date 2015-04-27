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

class Diib_Utils
{
    public static function load_textdomain() {
        load_plugin_textdomain( 'diib', false, basename( DIIB_PLUGIN_PATH ) . '/i18n/' );
    }

    public static function asset( $path ) {
        return DIIB_PLUGIN_URL . $path;
    }

    public static function is_authorized() {
        return Diib_OAuth::is_access_token_valid();
    }

    public static function get_tracker_label( $code = null ) {
        if( $code === null ) {
            $code = static::get_tracker_type();
        }

        switch( $code ) {
            case 'diib':
                return __( 'Diib Tracker', 'diib' );
            case 'piwik':
                return __( 'Self-Hosted Piwik', 'diib' );
            case 'ganalytics':
                return __( 'Google Analytics', 'diib' );
            default:
                return __( 'Unknown Tracker', 'diib' );
        }
    }

    public static function is_website_configured() {
        return ( int )get_option( 'diib_website_id' ) > 0;
    }

    public static function is_woocommerce_installed() {
        return in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
    }

    public static function should_track() {
        return static::is_website_configured() && defined( 'ENABLE_DIIB_TRACKING_CODE' ) && ENABLE_DIIB_TRACKING_CODE && get_option( 'diib_tracking_enabled' ) == '1';
    }

    public static function should_track_ecommerce() {
        return static::should_track() && static::is_woocommerce_installed() && get_option( 'diib_track_ecommerce' ) == '1';
    }

    public static function get_score( $date = 'yesterday', $force_pull = false, $ttl = 3600 ) {
        if( !static::is_website_configured() ) {
            return '';
        }

        $transient_key = sprintf( 'diib_health_score_%s_%s', get_option( 'diib_website_id' ), $date );

        $score = get_transient( $transient_key );

        if( $force_pull || $score === false ) {
            $score = Diib_Unirest::raw( Diib_Unirest::get( '/sites/{site}/scores?date=' . $date ) );

            set_transient( $transient_key, $score, 86400 );
        }

        return $score;
    }

    public static function get_tracker_type( $force_pull = false ) {
        if( !static::is_website_configured() ) {
            return '';
        }

        $transient_key = sprintf( 'diib_tracker_type_%s', get_option( 'diib_website_id' ) );

        $type = get_transient( $transient_key );

        if( $force_pull || $type === false ) {
            $response = Diib_Unirest::jsonify( Diib_Unirest::get( '/sites/{site}' ) );
            $type = $response['tracker']['type'];

            set_transient( $transient_key, $type, 86400 );
        }

        return $type;
    }

    public static function get_tracking_code( $force_pull = false ) {
        if( !static::is_website_configured() ) {
            return '';
        }

        $transient_key = sprintf( 'diib_tracking_code_%s', get_option( 'diib_website_id' ) );

        $code = get_transient( $transient_key );

        if( $force_pull || $code === false ) {
            $code = Diib_Unirest::plaintext( Diib_Unirest::get( '/sites/{site}/tracking-code' ) );

            set_transient( $transient_key, $code, 86400 );
        }

        return $code;
    }
}