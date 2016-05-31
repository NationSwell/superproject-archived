<?php
/**
 * Bitly - Simple PHP wrapper for the Bitly v3.0 API
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation. You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * You should have received a copy of the GNU General Public License along with this program; if not, write
 * to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 * @version 1.0-alpha
 * @author Luis Alberto Ochoa Esparza <soy@luisalberto.org>
 * @copyright Copyright (C) 2011-2012, Luis Alberto Ochoa Esparza
 * @link https://github.com/albertochoa/bitly/wiki
 */

class BitlyServiceError extends Exception {}


class BitlyService {

    private $oauth_token = '';

    /**
     * @since 1.0
     */
    public function __call( $method, $parameters ) {

        if ( 'oauth' == $method && !empty( $parameters ) )
            $this->oauth_token = $parameters[0];

        else
            throw new Exception( 'Call to undefined function.' );
    }

    public function hasToken()
    {
        return $this->oauth_token != '';
    }

    public function hasCurl()
    {
        return function_exists('curl_init');
    }

    /**
     * Given a long URL, returns a bitly short URL.
     *
     * @since 1.0
     * @param string $longUrl A long URL to be shortened (example: http://betaworks.com/).
     * @param string $domain Refers to a preferred domain; either bit.ly, j.mp, or bitly.com, for users who do NOT have a custom short domain set up with bitly.
     * @return string Returns a bitly short URL.
     */
    public function shorten( $longUrl, $domain = 'bit.ly' ) {

        $json = '';
        $shortlink = '';

        $args['longUrl'] = urlencode( $longUrl );
        $args['domain'] = $domain;

        $json = $this->get_bitly_service( 'shorten', $args );

        if ( empty( $json ) )
            return false;

        return $json['data'];
    }

    /**
     * Given a bitly URL or hash (or multiple), returns the target (long) URL.
     *
     * @since 1.0
     * @param array|string $parameters Refers to one or more bitly links/hashes.
     * @return array|string the URL that the requested short_url or hash points to.
     */
    public function expand( $parameters ) {

        $args = $this->get_multiple_query( $parameters );

        $json = $this->get_bitly_service( 'expand', $args );

        if ( empty( $json ) )
            return false;

        $expand = $json['data']['expand'];

        $count = count( $expand );

        if ( 1 === $count )
            $longUrl = ( isset( $expand[0]['long_url'] ) ? $expand[0]['long_url'] : $expand[0]['error'] );

        else
            $longUrl = $expand;

        return $longUrl;
    }

    /**
     * Query whether a given domain is a valid bitly pro domain.
     *
     * @since 1.0
     * @param string $domain A short domain. ie: nyti.ms.
     * @return bool True/False designating whether this is a current bitly domain.
     */
    public function pro( $domain ) {

        $args['domain'] = $domain;

        $json = $this->get_bitly_service( 'bitly_pro_domain', $args );

        if ( empty( $json ) )
            return false;

        return (bool) $json['data']['bitly_pro_domain'];
    }

    /**
     * This is used to return the page title for a given bitly link.
     *
     * @since 1.0
     * @param array|string $parameters Refers to one or more bitly links/hashes.
     * @return array $info
     */
    public function info( $parameters ) {

        $args = $this->get_multiple_query( $parameters );

        $json = $this->get_bitly_service( 'info', $args );

        if ( empty( $json ) )
            return false;

        $count = count( $json['data']['info'] );

        if ( 1 === $count )
            $info = $json['data']['info'][0];

        else
            $info = $json['data']['info'];

        return $info;
    }

    public function get_custom_domain()
    {
        $json = '';

        $json = $this->get_bitly_service( 'user/info', array() );

        if ( empty( $json ) )
            return false;

        error_log(print_r($json['data'], true));

        return $json['data']['custom_short_domain'];
    }

    public function get_most_popular($total)
    {
        $json = '';

        $args['limit'] = $total;

        $json = $this->get_bitly_service( 'user/popular_links', $args );

        if ( empty( $json ) )
            return false;

        return $json['data'];
    }

    public function get_most_recent($total)
    {
        $json = '';

        $args['limit'] = $total;
        $args['private'] = 'off';

        $json = $this->get_bitly_service( 'user/link_history', $args );

        if ( empty( $json ) )
            return false;

        return $json['data'];
    }

    public function search($total, $query)
    {
        $json = '';

        $args['limit'] = $total;
        $args['query'] = $query;

        $json = $this->get_bitly_service( 'search', $args );

        if ( empty( $json ) )
            return false;

        return $json['data'];
    }

    public function userInfo()
    {
        $json = '';

        $json = $this->get_bitly_service( 'user/info', array() );

        if ( empty( $json ) )
            return false;

        error_log(print_r($json['data'], true));

        return $json['data'];
    }

    /**
     * @since 1.0
     * @access private
     * @param array|string $args
     */
    private function get_multiple_query( $args = '' ) {

        if ( is_string( $args ) ) {

            if ( preg_match( '#http://#i', $args ) )
                $param['shortUrl'] = urlencode( $args );

            else
                $param['hash'] = $args;
        }

        else if ( is_array( $args ) ) {

            $short = array();
            $hash = array();

            foreach( $args as $arg ) {

                if ( preg_match( '#http://#i', $arg ) )
                    $short[] = urlencode( $arg );
                else
                    $hash[] = $arg;
            }

            if ( !empty( $short ) )
                $param['shortUrl'] = join( '&shortUrl=', $short );

            if ( !empty( $hash ) )
                $param['hash'] = join( '&hash=', $hash );
        }

        return $param;
    }

    /**
     * @since 1.0
     * @access private
     * @param string $service Servicio al que se realizará la petición
     * @param array $parameters Parámetros para especificar la salida de la petición
     */
    private function get_bitly_service( $service, $parameters ) {

        $response = '';

        $parameters['access_token'] = $this->oauth_token;

        foreach( $parameters as $parameter => $value )
            $query[] = "{$parameter}={$value}";

        $query = join( '&', $query );

        // https://api-ssl.bitly.com
        // /v3/shorten?access_token=ACCESS_TOKEN&longUrl=http%3A%2F%2Fgoogle.com%2F

        $url = "https://api-ssl.bitly.com/v3/{$service}?{$query}";

        $handle = curl_init();

        curl_setopt( $handle, CURLOPT_CONNECTTIMEOUT, 5 );
        curl_setopt( $handle, CURLOPT_TIMEOUT, 5 );
        curl_setopt( $handle, CURLOPT_URL, $url );
        curl_setopt( $handle, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $handle, CURLOPT_HEADER, false );

        $response = curl_exec( $handle );

        $errno = curl_errno( $handle );
        if ( $errno != CURLE_OK )
            throw new BitlyServiceError(curl_error( $handle ), $errno);

        curl_close( $handle );

        error_log(print_r($response, true));

        if ( !empty( $response ) )
        {
            $response = @json_decode( $response, true );
            if ( $response === null )
                throw new BitlyServiceError('JSON could not be decoded', -1);

            if ( 200 == $response['status_code'] && 'OK' == $response['status_txt'] )
                return $response;
            else
                throw new BitlyServiceError($response['status_txt'], $response['status_code']);
        }

        return false;
    }
}
