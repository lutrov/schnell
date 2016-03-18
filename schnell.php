<?php

/*
Plugin Name: Schnell
Description: Improves the speed of your site by enabling client side caching with "expire" headers, "cache-control" headers &amp; output compression. Why this plugin name? Schnell means "quick" in German.
Version: 1.7
Author: Ivan Lutrov
Author URI: http://lutrov.com/
*/

defined('ABSPATH') || die('Ahem.');

//
// Define constants ised by this plugin.
//
define('SCHNELL_EXPIRY_ONE_YEAR', 31449600);
define('SCHNELL_EXPIRY_ONE_MONTH', 2592000);
define('SCHNELL_EXPIRY_ONE_WEEK', 604800);

//
// Write .htaccess file.
//
function schnell_htaccess_file($action) {
	if (is_writable(ABSPATH . '.htaccess')) {
		$content = file_get_contents(ABSPATH . '.htaccess');
		$rules = schnell_htaccess_rules();
		$temp = '[[' . hash('sha1', $rules) . ']]';
		$content = preg_replace('/# BEGIN Schnell Optimisation(.+)# END Schnell Optimisation/isU', $temp, $content, -1, $count);
		switch ($action) {
			case 'install':
				if ($count > 0) {
					$content = trim(str_replace($temp, $rules, $content));
				} else {
					$content = trim($content) . PHP_EOL . PHP_EOL . $rules;
				}
				break;
			case 'uninstall':
				if ($count > 0) {
					$content = trim(str_replace($temp, null, $content)) . PHP_EOL;
				}
				break;
		}
		return file_put_contents(ABSPATH . '.htaccess', $content);
	}
}

//
// Build .htaccess rules.
//
function schnell_htaccess_rules() {
	$result  = '# BEGIN Schnell Optimisation' . PHP_EOL;
	$result .= '# Compress text files' . PHP_EOL;
	$result .= '<ifModule mod_deflate.c>' . PHP_EOL;
	$result .= 'AddOutputFilterByType DEFLATE text/html text/xml text/css text/plain' . PHP_EOL;
	$result .= 'AddOutputFilterByType DEFLATE image/svg+xml application/xhtml+xml application/xml' . PHP_EOL;
	$result .= 'AddOutputFilterByType DEFLATE application/rdf+xml application/rss+xml application/atom+xml' . PHP_EOL;
	$result .= 'AddOutputFilterByType DEFLATE text/javascript application/javascript application/x-javascript application/json' . PHP_EOL;
	$result .= '</ifModule>' . PHP_EOL;
	$result .= '# Compress fonts' . PHP_EOL;
	$result .= '<ifModule mod_deflate.c>' . PHP_EOL;
	$result .= 'AddOutputFilterByType DEFLATE application/x-font-ttf application/x-font-otf' . PHP_EOL;
	$result .= 'AddOutputFilterByType DEFLATE font/truetype font/opentype' . PHP_EOL;
	$result .= '</ifModule>' . PHP_EOL;
	$result .= '# Set expire headers' . PHP_EOL;
	$result .= '<ifModule mod_expires.c>' . PHP_EOL;
	$result .= 'ExpiresActive On' . PHP_EOL;
	$result .= 'ExpiresDefault A60' . PHP_EOL;
	$result .= '# Font files expire in 1 year' . PHP_EOL;
	$result .= 'ExpiresByType font/otf A' . SCHNELL_EXPIRY_ONE_YEAR . PHP_EOL;
	$result .= 'ExpiresByType font/x-woff A' . SCHNELL_EXPIRY_ONE_YEAR . PHP_EOL;
	$result .= 'ExpiresByType application/font-woff2 A' . SCHNELL_EXPIRY_ONE_YEAR . PHP_EOL;
	$result .= 'ExpiresByType font/ttf A' . SCHNELL_EXPIRY_ONE_YEAR . PHP_EOL;
	$result .= '# Image files expire in 1 month' . PHP_EOL;
	$result .= 'ExpiresByType image/x-icon A' . SCHNELL_EXPIRY_ONE_MONTH . PHP_EOL;
	$result .= 'ExpiresByType image/jpeg A' . SCHNELL_EXPIRY_ONE_MONTH . PHP_EOL;
	$result .= 'ExpiresByType image/png A' . SCHNELL_EXPIRY_ONE_MONTH . PHP_EOL;
	$result .= 'ExpiresByType image/gif A' . SCHNELL_EXPIRY_ONE_MONTH . PHP_EOL;
	$result .= 'ExpiresByType application/x-shockwave-flash A' . SCHNELL_EXPIRY_ONE_MONTH . PHP_EOL;
	$result .= '# Stylesheet & Javascript files expire in 1 week' . PHP_EOL;
	$result .= 'ExpiresByType text/css A' . SCHNELL_EXPIRY_ONE_WEEK . PHP_EOL;
	$result .= 'ExpiresByType text/javascript A' . SCHNELL_EXPIRY_ONE_WEEK . PHP_EOL;
	$result .= 'ExpiresByType application/javascript A' . SCHNELL_EXPIRY_ONE_WEEK . PHP_EOL;
	$result .= 'ExpiresByType application/x-javascript A' . SCHNELL_EXPIRY_ONE_WEEK . PHP_EOL;
	$result .= '# HTML files do not expire' . PHP_EOL;
	$result .= 'ExpiresByType text/html A0' . PHP_EOL;
	$result .= 'ExpiresByType application/xhtml+xml A0'. PHP_EOL;
	$result .= '</ifModule>' . PHP_EOL;
	$result .= '# Set cache-control headers' . PHP_EOL;
	$result .= '<ifModule mod_headers.c>' . PHP_EOL;
	$result .= '<filesMatch "\.(otf|woff2?|ttf)$">' . PHP_EOL;
	$result .= 'Header set Cache-Control "public, max-age=' . SCHNELL_EXPIRY_ONE_YEAR . '"' . PHP_EOL;
	$result .= '</filesMatch>' . PHP_EOL;
	$result .= '<filesMatch "\.(ico|jpe?g|png|gif|swf)$">' . PHP_EOL;
	$result .= 'Header set Cache-Control "public, max-age=' . SCHNELL_EXPIRY_ONE_MONTH . '"' . PHP_EOL;
	$result .= '</filesMatch>' . PHP_EOL;
	$result .= '<filesMatch "\.(css)$">' . PHP_EOL;
	$result .= 'Header set Cache-Control "public, max-age=' . SCHNELL_EXPIRY_ONE_WEEK . '"' . PHP_EOL;
	$result .= '</filesMatch>' . PHP_EOL;
	$result .= '<filesMatch "\.(js)$">' . PHP_EOL;
	$result .= 'Header set Cache-Control "private, max-age=' . SCHNELL_EXPIRY_ONE_WEEK . '"' . PHP_EOL;
	$result .= '</filesMatch>' . PHP_EOL;
	$result .= '<filesMatch "\.(x?html?|php)$">' . PHP_EOL;
	$result .= 'Header set Cache-Control "private, must-revalidate, max-age=0"' . PHP_EOL;
	$result .= '</filesMatch>' . PHP_EOL;
	$result .= '# Set useful headers' . PHP_EOL;
	$result .= 'Header merge Cache-Control "no-transform"' . PHP_EOL;
	$result .= 'Header set X-Content-Type-Options "nosniff"' . PHP_EOL;
	$result .= '# Unset useless headers' . PHP_EOL;
	$result .= 'Header unset X-Frame-Options' . PHP_EOL;
	$result .= 'Header unset X-Pingback' . PHP_EOL;
	$result .= 'Header unset X-Powered-By' . PHP_EOL;
	$result .= '</ifModule>' . PHP_EOL;
	$result .= '# Turn off ETags' . PHP_EOL;
	$result .= 'Header unset ETag' . PHP_EOL;
	$result .= 'FileETag None' . PHP_EOL;
	$result .= '# END Schnell Optimisation' . PHP_EOL;
	return $result;
}

//
// Activation and deactivation hooks.
//
function schnell_activate() {
	schnell_htaccess_file('install');
}
function schnell_deactivate() {
	schnell_htaccess_file('uninstall');
}
register_activation_hook(__FILE__, 'schnell_activate');
register_deactivation_hook(__FILE__, 'schnell_deactivate');

?>