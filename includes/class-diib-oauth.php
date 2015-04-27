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

class Diib_OAuth
{
    public static function has_access_token() {
        return static::_get_access_token() !== false;
    }

    public static function get_access_token() {
        return static::_get_access_token();
    }

    public static function is_access_token_valid() {
        return static::has_access_token() && static::_get_access_token_expiration() > time();
    }

    public static function refresh_token() {
        $payload = array(
            'grant_type' => 'refresh_token',
            'refresh_token' => static::_get_refresh_token(),
            'client_id' => DIIB_OAUTH2_CLIENT_ID,
        );

        $response = Unirest::post( DIIB_OAUTH2_ENDPOINT . '/token', array(
            'Accept' => 'application/json;text/plain',
        ), $payload );

        $json = $response->code == 200 ? ( array )$response->body : null;

        if( is_array( $json ) && !empty( $json ) ) {
            update_option( 'diib_access_token', $json['access_token'] );
            update_option( 'diib_access_token_expiration', time() + $json['expires_in'] );
            update_option( 'diib_refresh_token', $json['refresh_token'] );
        }
    }

    protected static function _get_access_token() {
        return get_option( 'diib_access_token' );
    }

    protected static function _get_refresh_token() {
        return get_option( 'diib_refresh_token' );
    }

    protected static function _get_access_token_expiration() {
        return ( int )get_option( 'diib_access_token_expiration' );
    }
}