<?php

/*
Plugin Name: Schnell
Description: Improves the speed of your site by enabling client side caching with "expire" headers, "cache-control" headers &amp; output compression. Why this plugin name? Schnell means "quick" in German.
Version: 1.8
Author: Ivan Lutrov
Author URI: http://lutrov.com/
*/

defined('ABSPATH') || die('Ahem.');

//
// Write .htaccess file.
//
function schnell_htaccess_file($action) {
	$path = sprintf('%s/.htaccess', rtrim(ABSPATH, '/'));
	if (is_writable($path) == true) {
		if (($fp = fopen($path, 'r'))) {
			$content = fread($fp, filesize($path));
			fclose($fp);
			$rules = schnell_htaccess_rules();
			$hash = sprintf('[[%s]]', hash('sha1', $rules));
			$content = preg_replace('/# BEGIN Schnell Optimisation(.+)# END Schnell Optimisation/isU', $hash, $content, -1, $count);
			switch ($action) {
				case 'install':
					if ($count > 0) {
						$content = trim(str_replace($hash, $rules, $content));
					} else {
						$content = sprintf('%s%s%s%s', trim($content), PHP_EOL, PHP_EOL, $rules);
					}
					break;
				case 'uninstall':
					if ($count > 0) {
						$content = sprintf('%s%s', trim(str_replace($hash, null, $content)), PHP_EOL);
					}
					break;
			}
			if (($fp = fopen($path, 'w'))) {
				fwrite($fp, $content, strlen($content));
				fclose($fp);
			}
		}
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
	$result .= 'ExpiresByType font/otf A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= 'ExpiresByType font/x-woff A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= 'ExpiresByType application/font-woff2 A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= 'ExpiresByType font/ttf A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= '# Image files expire in 1 month' . PHP_EOL;
	$result .= 'ExpiresByType image/x-icon A' . MONTH_IN_SECONDS . PHP_EOL;
	$result .= 'ExpiresByType image/jpeg A' . MONTH_IN_SECONDS . PHP_EOL;
	$result .= 'ExpiresByType image/png A' . MONTH_IN_SECONDS . PHP_EOL;
	$result .= 'ExpiresByType image/gif A' . MONTH_IN_SECONDS . PHP_EOL;
	$result .= 'ExpiresByType application/x-shockwave-flash A' . MONTH_IN_SECONDS . PHP_EOL;
	$result .= '# Stylesheet & Javascript files expire in 1 week' . PHP_EOL;
	$result .= 'ExpiresByType text/css A' . WEEK_IN_SECONDS . PHP_EOL;
	$result .= 'ExpiresByType text/javascript A' . WEEK_IN_SECONDS . PHP_EOL;
	$result .= 'ExpiresByType application/javascript A' . WEEK_IN_SECONDS . PHP_EOL;
	$result .= 'ExpiresByType application/x-javascript A' . WEEK_IN_SECONDS . PHP_EOL;
	$result .= '# HTML files do not expire' . PHP_EOL;
	$result .= 'ExpiresByType text/html A0' . PHP_EOL;
	$result .= 'ExpiresByType application/xhtml+xml A0'. PHP_EOL;
	$result .= '</ifModule>' . PHP_EOL;
	$result .= '# Set cache-control headers' . PHP_EOL;
	$result .= '<ifModule mod_headers.c>' . PHP_EOL;
	$result .= '<filesMatch "\.(otf|woff2?|ttf)$">' . PHP_EOL;
	$result .= 'Header set Cache-Control "public, max-age=' . YEAR_IN_SECONDS . '"' . PHP_EOL;
	$result .= '</filesMatch>' . PHP_EOL;
	$result .= '<filesMatch "\.(ico|jpe?g|png|gif|swf)$">' . PHP_EOL;
	$result .= 'Header set Cache-Control "public, max-age=' . MONTH_IN_SECONDS . '"' . PHP_EOL;
	$result .= '</filesMatch>' . PHP_EOL;
	$result .= '<filesMatch "\.(css)$">' . PHP_EOL;
	$result .= 'Header set Cache-Control "public, max-age=' . WEEK_IN_SECONDS . '"' . PHP_EOL;
	$result .= '</filesMatch>' . PHP_EOL;
	$result .= '<filesMatch "\.(js)$">' . PHP_EOL;
	$result .= 'Header set Cache-Control "private, max-age=' . WEEK_IN_SECONDS . '"' . PHP_EOL;
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
// Register plugin activation hook.
//
register_activation_hook(__FILE__, 'schnell_activate');
function schnell_activate() {
	schnell_htaccess_file('install');
}

//
// Register plugin deactivation hook.
//
register_deactivation_hook(__FILE__, 'schnell_deactivate');
function schnell_deactivate() {
	schnell_htaccess_file('uninstall');
}

?>