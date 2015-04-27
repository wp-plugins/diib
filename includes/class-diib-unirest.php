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

class Diib_Unirest extends Unirest
{
    protected static function request($httpMethod, $url, $body = NULL, $headers = array(), $username = NULL, $password = NULL) {
        if( $headers === NULL ) {
            $headers = array();
        }

        if( strpos($url, 'http') !== 0 ) {
            $url = DIIB_ENDPOINT . '/api' . $url;
        }

        $url = str_replace( '{site}', get_option( 'diib_website_id' ), $url );

        $headers['Authorization'] = sprintf( 'Bearer %s', Diib_OAuth::get_access_token() );
        $headers['Accept'] = 'application/json;text/plain';

        return parent::request( $httpMethod, $url, $body, $headers, $username, $password );
    }

    public static function plaintext($response) {
        if( $response->code === 200 ) {
            return $response->body;
        }

        return '';
    }

    public static function raw($response) {
        return $response->raw_body;
    }

    public static function jsonify($response) {
        if( $response->code === 200 ) {
            return ( array )$response->body;
        }

        return null;
    }
}