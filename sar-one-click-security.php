<?php
/*
Plugin Name: SAR One Click Security
Plugin URI: http://www.samuelaguilera.com/archivo/protege-wordpress-facilmente.xhtml
Description: Adds some extra security to your WordPress with only one click.
Author: Samuel Aguilera
Version: 1.1.1
Author URI: http://www.samuelaguilera.com
License: GPL3
*/

/*
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License version 3 as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// TODO: Convert settigs individual settings to an array... (when I'm sure that no more settings are planned) 

if ( !defined( 'ABSPATH' ) ) { exit; } // Not needed in this case, but maybe in the future...

// Current plugin version
define('SAR_OCS_VER', 109);

function SAR_OCS_Init() {

	global $is_apache;

	// Load language file first
	load_plugin_textdomain( 'sar-one-click-security', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	if ( !$is_apache ) {

			function SAR_Apache_Not_Found() {
			    ?>
			    <div class="error">
			        <p><?php _e( '<strong>SAR One Click Security only supports Apache servers</strong>. Your server is not Apache, so there was no change. You should deactivate and delete this plugin.', 'sar-one-click-security' ); ?></p>
			    </div>
			    <?php
			}
			add_action( 'admin_notices', 'SAR_Apache_Not_Found' );

	}

	// Needs upgrade?
	$current_ver = get_option('sar_ocs_ver');

	if ( false === $current_ver || $current_ver < SAR_OCS_VER ) {

		// Upgrade rules
		SAR_Remove_Security_Rules();
		SAR_Add_Security_Rules();

		// Update current ver to DB
		update_option( 'sar_ocs_ver', SAR_OCS_VER );

	}	

}

add_action( 'admin_init', 'SAR_OCS_Init' );


function SAR_OCS_Activation(){

	// Adds current ver to DB
	add_option( 'sar_ocs_ver', SAR_OCS_VER );

	// Checks if user 'admin' exists
	$wp_users = get_users( array( role=> 'administrator', fields => array( 'user_login' ) ) );

	foreach ( $wp_users as $user ) {
		if ( $user->user_login === 'admin' ) { $has_admin = true; break; }
	}

	if ( !$has_admin ) { add_option( 'sar_ocs_block_admin', 'yes' ); }

	// TODO: Create function to manage blocking of admin user login.

	// Install security rules
	SAR_Add_Security_Rules();

}

function SAR_OCS_Deactivation(){

	// Remove security rules
	SAR_Remove_Security_Rules();

	// Remove options stored
	delete_option( 'sar_ocs_ver' );
	delete_option( 'sar_ocs_block_admin' );	
}

register_activation_hook( __FILE__, 'SAR_OCS_Activation' );
register_deactivation_hook( __FILE__, 'SAR_OCS_Deactivation' );


function SAR_Add_Security_Rules(){

	global $is_apache;

	if ( $is_apache ) {

		// Path to .htaccess
		$htaccess = get_home_path().".htaccess";
		$wp_content_htaccess = WP_CONTENT_DIR.'/.htaccess';

		// WordPress domain
		$wp_url = get_bloginfo( 'wpurl' );
		$wp_url = parse_url($wp_url);
		$wp_domain = preg_replace('#^www\.(.+\.)#i', '$1', $wp_url['host']); // only removes www from beginning, allowing domains that contains www on it
		$wp_domain = explode(".",$wp_domain);

		// Support for multisite subdomains
		$domain_parts = count($wp_domain);
		if ($domain_parts === 2) {
			$wp_domain_exploded = $wp_domain[0].'\.'.$wp_domain[1];
		} elseif ($domain_parts === 3) {
			$wp_domain_exploded = $wp_domain[0].'\.'.$wp_domain[1].'\.'.$wp_domain[2];
		} else {
			$wp_domain_not_supported = true; // for IP based URLs
		}

		// Security rules	 
		$sec_rules = array();
		$sec_rules[] = "# Any decent hosting should have this set, but many don't have";
		$sec_rules[] = 'ServerSignature Off'.PHP_EOL.'<IfModule mod_autoindex.c>'.PHP_EOL.'IndexIgnore *'.PHP_EOL.'</IfModule>'; // Options -Indexes maybe is better, but some hostings doesn't allow the use of Options directives from .htaccess

		$sec_rules[] = '# Block access to sensitive files';
		$sec_rules[] = "<Files .htaccess>".PHP_EOL."order allow,deny".PHP_EOL."deny from all".PHP_EOL."</Files>";
		$sec_rules[] = '<FilesMatch "^(license|readme|wp-config|wp-config-sample).*$">'.PHP_EOL.'order allow,deny'.PHP_EOL.'deny from all'.PHP_EOL.'</FilesMatch>';

		$sec_rules[] = '# Stops dummy bots trying to register in WordPress sites that have registration disabled';
		$sec_rules[] = '<IfModule mod_rewrite.c>'.PHP_EOL.'RewriteEngine On';
		$sec_rules[] = 'RewriteCond %{QUERY_STRING} ^action=register$ [NC,OR]'.PHP_EOL.'RewriteCond %{HTTP_REFERER} ^.*registration=disabled$ [NC]'.PHP_EOL.'RewriteRule (.*) http://127.0.0.1 [L,R=301]';
		$sec_rules[] = '</IfModule>';

		if ( !defined( 'SAR_ALLOW_TIMTHUMB' ) ) {
			$sec_rules[] = '# Block requests looking for timthumb.php';	
			$sec_rules[] = '<IfModule mod_rewrite.c>'.PHP_EOL.'RewriteEngine On';
			$sec_rules[] = 'RewriteRule ^(.*)/?timthumb\.php$ http://127.0.0.1 [L,R=301,NC,QSA]';
			$sec_rules[] = '</IfModule>';
		}

		$sec_rules[] = '# Block TRACE and TRACK request methods'; // TRACK is not availabe in Apache (without plugins) is a IIS method, but bots will try it anyway.
		$sec_rules[] = '<IfModule mod_rewrite.c>'.PHP_EOL.'RewriteEngine On';
	    $sec_rules[] = 'RewriteCond %{REQUEST_METHOD} ^(TRACE|TRACK)$';
	    $sec_rules[] = 'RewriteRule (.*) - [F]';
		$sec_rules[] = '</IfModule>';

		if (!$wp_domain_not_supported) { // We don't want to add this if the domain is not supported...
			$sec_rules[] = '# Blocks direct posting to wp-comments-post.php/wp-login.php and black User Agent';	
			$sec_rules[] = '<IfModule mod_rewrite.c>'.PHP_EOL.'RewriteEngine On';
			$sec_rules[] = 'RewriteCond %{REQUEST_METHOD} ^(PUT|POST)$ [NC]';
			$sec_rules[] = 'RewriteCond %{REQUEST_URI} ^.(wp-comments-post|wp-login)\.php$ [NC]';
			$sec_rules[] = 'RewriteCond %{HTTP_REFERER} !^.*'.$wp_domain_exploded.'.*$ [OR]';
			$sec_rules[] = 'RewriteCond %{HTTP_USER_AGENT} ^$';
			$sec_rules[] = 'RewriteRule (.*) http://127.0.0.1 [L,R=301]';
			$sec_rules[] = '</IfModule>';

		}

		// Insert rules to existing .htaccess or create new file if no .htaccess is present
		insert_with_markers($htaccess, "SAR One Click Security", $sec_rules);

		// Create .htacces for blocking direct access to PHP files in wp-content/ only if file .htaccess does not exists
		$wpc_htaccess_exists = file_exists ( $wp_content_htaccess );

		$wp_content_sec_rules = array();
		$wp_content_sec_rules[] = '<FilesMatch "\.(php)$">'.PHP_EOL.'order allow,deny'.PHP_EOL.'deny from all'.PHP_EOL.'</FilesMatch>';

		if ( defined( 'SAR_ALLOW_TIMTHUMB' ) ) {
			$wp_content_sec_rules[] = '# Allow requests looking for TimThumb';	
			$wp_content_sec_rules[] = '<FilesMatch "^(timthumb|thumb)\.php$">';
			$wp_content_sec_rules[] = 'Order Allow,Deny';
			$wp_content_sec_rules[] = 'Allow from all';
			$wp_content_sec_rules[] = '</FilesMatch>';
		}

		// Stores an option to be sure that we delete (in the future) a file that we have created
		if (!$wpc_htaccess_exists) { add_option( 'sar_ocs_wpc_htaccess', 'yes' ); } 

		// Insert rules to existing .htaccess or create new file if no .htaccess is present
		insert_with_markers($wp_content_htaccess, "SAR One Click Security", $wp_content_sec_rules);		

	}

}


function SAR_Remove_Security_Rules(){

	global $is_apache;

	if ( $is_apache ) {

		// Path to .htaccess
		$htaccess = get_home_path().".htaccess";
		$wp_content_htaccess = WP_CONTENT_DIR.'/.htaccess';

		$wp_content_htaccess_owned = get_option( 'sar_ocs_wpc_htaccess' );
		
		// Empty rules 
		$empty_sec_rules = array();
		
		// Remove rules. Markers will remain, but are only comments. TODO: Maybe create a new function to remove markers too. 
		insert_with_markers($htaccess, "SAR One Click Security", $empty_sec_rules);

		if ($wp_content_htaccess_owned === 'yes') {

			// Remove .htacces from wp-content that we have created
			unlink($wp_content_htaccess);
			delete_option('sar_ocs_wpc_htaccess');

		} else { // If the file was there before the plugin

			// Remove rules. Markers will remain, but are only comments. TODO: Maybe create a new function to remove markers too. 
			insert_with_markers($wp_content_htaccess, "SAR One Click Security", $empty_sec_rules);

		}

	}

}

?>