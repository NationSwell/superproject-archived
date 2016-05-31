<?php

/**
 * This file contains legacy code for the Humans TXT plugin.
 *
 * Copyright 2014 Till Krüss  (http://till.kruss.me/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @since 1.1
 * @package Humans TXT
 * @copyright 2014 Till Krüss
 */

/**
 * WP filter callback for 'admin_body_class'. Adds 'rtl' to
 * the body classes list, if is_rtl() is TRUE.
 *
 * @since 1.1.1
 *
 * @param string $classes
 * @return string $classes
 */
function humanstxt_admin_body_class( $classes ) {
	if ( is_rtl() && strpos( $classes, 'rtl' ) === false ) {
		$classes .= ' rtl ';
	}
	return $classes;
}
add_filter( 'admin_body_class', 'humanstxt_admin_body_class' );

if ( !function_exists( 'esc_textarea' ) ) :
/**
 * Escaping for textarea values introduced in WordPress 3.1.
 * Source: http://codex.wordpress.org/Function_Reference/esc_textarea
 */
function esc_textarea( $text ) {
	$safe_text = htmlspecialchars( $text, ENT_QUOTES );
	return apply_filters( 'esc_textarea', $safe_text, $text );
}
endif;

if ( !function_exists( 'str_ireplace' ) ) :
/**
 * Case-insensitive version of str_replace() for PHP4.
 * Source: http://pear.php.net/package/PHP_Compat/
 */
function str_ireplace( $search, $replace, $subject ) {
	// Sanity check
	if ( is_string( $search ) && is_array( $replace ) ) {
		user_error( 'Array to string conversion', E_USER_NOTICE );
		$replace = (string) $replace;
	}

	// If search isn't an array, make it one
	$search = (array) $search;
	$length_search = count( $search );

	// build the replace array
	$replace = is_array( $replace )
	? array_pad( $replace, $length_search, '' )
	: array_pad(array(), $length_search, $replace );

	// If subject is not an array, make it one
	$was_string = false;
	if ( is_string( $subject ) ) {
		$was_string = true;
		$subject = array( $subject );
	}

	// Prepare the search array
	foreach ( $search as $search_key => $search_value ) {
		$search[ $search_key ] = '/' . preg_quote( $search_value, '/' ) . '/i';
	}

	// Prepare the replace array (escape backreferences)
	$replace = str_replace( array( '\\', '$' ), array( '\\\\', '\$' ), $replace );

	$result = preg_replace( $search, $replace, $subject );
	return $was_string ? $result[0] : $result;
}
endif;

?>