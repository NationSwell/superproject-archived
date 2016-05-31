<?php
/*
Plugin Name: Simple Menu Delete
Plugin URI: http://www.codepress.nl/plugins/simple-menu-delete/
Description: Adds an extra delete button to your nav menu items.  
Author: Tobias Schutter, Codepress
Version: 0.2
Author URI: http://www.codepress.nl

Updates:
0.1 - First Version

	Copyright 2011 Codepress.nl

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

if(defined('CPSMD_VERSION')) return;

// Determine plugin directory
define( 'CPSMD_VERSION', '0.2' );
define( 'CPSMD_URL', plugin_dir_url(__FILE__) );

// add script
function codepress_admin_scripts() {    
    wp_enqueue_script( 'codepress-menu-backend-js', CPSMD_URL. '/js/menu_backend.js' );
}
add_action('admin_print_scripts', 'codepress_admin_scripts');
