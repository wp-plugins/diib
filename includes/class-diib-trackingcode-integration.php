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

require_once __DIR__ . '/class-diib-trackingcode-ga-integrator.php';
require_once __DIR__ . '/class-diib-trackingcode-piwik-integrator.php';
require_once __DIR__ . '/class-diib-trackingcode-diib-integrator.php';

class Diib_TrackingCode_Integration
{
    protected static $_integrators = array();

    public static function setup_integrators() {
        static::$_integrators = array(
            'ganalytics' => new Diib_TrackingCode_GA_Integrator(),
            'piwik' => new Diib_TrackingCode_Piwik_Integrator(),
            'diib' => new Diib_TrackingCode_Diib_Integrator(),
        );
    }

    public static function initialize() {
        add_action( 'wp_head', array( __CLASS__, 'initialize_integrations' ) );
        add_action( 'wp_footer', array( __CLASS__, 'integrate_tracking_code' ) );
    }

    public static function initialize_integrations() {
        if( ( $integrator = static::get_integrator() ) !== null ) {
            $integrator->tracker_specific_integrations();
        }
    }

    public static function integrate_tracking_code() {
        if( Diib_Utils::should_track_ecommerce() ) {
            echo static::apply_filters( 'diib_ecommerce_tracking_code', Diib_Utils::get_tracking_code() );
        } else {
            echo static::apply_filters( 'diib_tracking_code', Diib_Utils::get_tracking_code() );
        }
    }

    public static function apply_filters( $filter_name, $tracking_code ) {
        return str_replace( '// ADDITIONAL SETUP HERE', apply_filters( $filter_name, '' ), $tracking_code );
    }

    public static function get_integrator() {
        $type = Diib_Utils::get_tracker_type();

        if( array_key_exists( $type, static::$_integrators ) ) {
            return static::$_integrators[$type];
        }

        return null;
    }
}