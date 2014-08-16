<?php
/*
Plugin Name: SAR One Click Security
Plugin URI: http://www.samuelaguilera.com/
Description: Adds some extra security to your WordPress with only one click. No options page, just activate it!
Author: Samuel Aguilera
Version: 1.0.6
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

// Current plugin version
define('SAR_OCS_VER', 106);

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

		// Adds current ver to DB
		add_option( 'sar_ocs_ver', SAR_OCS_VER );

	}

}

add_action( 'plugins_loaded', 'SAR_OCS_Init' );


function SAR_OCS_Activation(){

	// Adds current ver to DB
	add_option( 'sar_ocs_ver', SAR_OCS_VER );

	// Install security rules
	SAR_Add_Security_Rules();

}

function SAR_OCS_Deactivation(){

	// Remove security rules
	SAR_Remove_Security_Rules();

	// Remove plugin ver from DB
	delete_option( 'sar_ocs_ver' );
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
		$sec_rules[] = 'RewriteCond %{QUERY_STRING} (registration=disabled|action=register) [NC,OR]'.PHP_EOL.'RewriteCond %{HTTP_REFERER} registration=disabled [NC]'.PHP_EOL.'RewriteRule ^wp-login.php http://127.0.0.1 [L,R=301]';
		$sec_rules[] = '</IfModule>';

		$sec_rules[] = '# Block requests looking for timthumb.php';	
		$sec_rules[] = '<IfModule mod_rewrite.c>'.PHP_EOL.'RewriteEngine On';
		$sec_rules[] = 'RewriteRule ^(.*)/?timthumb\.php$ http://127.0.0.1 [L,R=301,NC,QSA]';
		$sec_rules[] = '</IfModule>';

		$sec_rules[] = '# Block TRACE and TRACK request methods';	
		$sec_rules[] = '<IfModule mod_rewrite.c>'.PHP_EOL.'RewriteEngine On';
	    $sec_rules[] = 'RewriteCond %{REQUEST_METHOD} ^(TRACE|TRACK)';
	    $sec_rules[] = 'RewriteRule (.*) - [F]';
		$sec_rules[] = '</IfModule>';

		if (!$wp_domain_not_supported) { // We don't want to add this if the domain is not supported...
			$sec_rules[] = '# Blocks direct posting to wp-comments-post.php';	
			$sec_rules[] = '<IfModule mod_rewrite.c>'.PHP_EOL.'RewriteEngine On';
			$sec_rules[] = 'RewriteCond %{REQUEST_METHOD} ^(PUT|POST|GET)$ [NC]';
			$sec_rules[] = 'RewriteCond %{REQUEST_URI} .wp-comments-post\.php*';
			$sec_rules[] = 'RewriteCond %{HTTP_REFERER} !^http(s)://(www\.)?'.$wp_domain_exploded.'/.*$ [OR]';
			$sec_rules[] = 'RewriteCond %{HTTP_USER_AGENT} ^$';
			$sec_rules[] = 'RewriteRule (.*) http://127.0.0.1 [L,R=301]';
			$sec_rules[] = '</IfModule>';
		}

		// Insert rules to .htaccess
		insert_with_markers($htaccess, "SAR One Click Security", $sec_rules);

		// Create .htacces for blocking direct access to PHP files in wp-content/ only if file .htaccess does not exists
		$wpc_htaccess_exists = file_exists ( $wp_content_htaccess );

		$wp_content_sec_rules = array();
		$wp_content_sec_rules[] = '<FilesMatch "\.(php)$">'.PHP_EOL.'order allow,deny'.PHP_EOL.'deny from all'.PHP_EOL.'</FilesMatch>';


		if (!$wpc_htaccess_exists) {

			file_put_contents($wp_content_htaccess, $wp_content_sec_rules, LOCK_EX);

			// Stores an option to be sure that we delete (in the future) a file that we have created
			add_option( 'sar_ocs_wpc_htaccess', 'yes' );	

		} else { //If this file already exists... (rare but who knows!)

			// Insert rules to existing .htaccess
			insert_with_markers($wp_content_htaccess, "SAR One Click Security", $wp_content_sec_rules);

		}

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