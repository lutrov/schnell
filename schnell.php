<?php

/*
Plugin Name: Schnell
Description: Improves the speed of your site by enabling client side caching with "expire" headers, "cache-control" headers &amp; output compression. Why this plugin name? Schnell means "quick" in German.
Plugin URI: https://github.com/lutrov/schnell
Author: Ivan Lutrov
Author URI: http://lutrov.com/
Version: 3.4
*/

defined('ABSPATH') || die('Ahem.');

//
// Define constants used by this plugin.
//
define('SCHNELL_CACHE_LIFETIME', '691200'); // 8 days

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
	$result .= '<IfModule mod_headers.c>' . PHP_EOL;
	$result .= '# Set cache control headers' . PHP_EOL;
	$result .= 'Header set Vary "Accept-Encoding"' . PHP_EOL;
	$result .= '# Images expire after 1 month' . PHP_EOL;
	$result .= '<FilesMatch "\.(jpg|jpeg|png|gif|ico)$">' . PHP_EOL;
	$result .= 'Header set Cache-Control "max-age=' . SCHNELL_CACHE_LIFETIME . ', public"' . PHP_EOL;
	$result .= '</FilesMatch>' . PHP_EOL;
	$result .= '# Scripts expire after 1 month' . PHP_EOL;
	$result .= '<FilesMatch "\.(css|js)$">' . PHP_EOL;
	$result .= 'Header set Cache-Control "max-age=' . SCHNELL_CACHE_LIFETIME . ', public"' . PHP_EOL;
	$result .= '</FilesMatch>' . PHP_EOL;
	$result .= '# Fonts expire after 1 month' . PHP_EOL;
	$result .= '<FilesMatch "\.(woff|woff2|ttf|otf)$">' . PHP_EOL;
	$result .= 'Header set Cache-Control "max-age=' . SCHNELL_CACHE_LIFETIME . ', public"' . PHP_EOL;
	$result .= '</FilesMatch>' . PHP_EOL;
	$result .= '# Archives expire after 1 month' . PHP_EOL;
	$result .= '<FilesMatch "\.(zip|7z|tar)$">' . PHP_EOL;
	$result .= 'Header set Cache-Control "max-age=' . SCHNELL_CACHE_LIFETIME . ', public"' . PHP_EOL;
	$result .= '</FilesMatch>' . PHP_EOL;
	$result .= '# Documents expire after 1 month' . PHP_EOL;
	$result .= '<FilesMatch "\.(pdf|docx|xlsx|doc|xls|otd|ods)$">' . PHP_EOL;
	$result .= 'Header set Cache-Control "max-age=' . SCHNELL_CACHE_LIFETIME . ', public"' . PHP_EOL;
	$result .= '</FilesMatch>' . PHP_EOL;
	$result .= '# Set other useful headers' . PHP_EOL;
	$result .= 'Header set X-Content-Type-Options "nosniff"' . PHP_EOL;
	$result .= '# Unset useless headers' . PHP_EOL;
	$result .= 'Header unset X-Frame-Options' . PHP_EOL;
	$result .= 'Header unset X-Pingback' . PHP_EOL;
	$result .= 'Header unset X-Powered-By' . PHP_EOL;
	$result .= '</IfModule>' . PHP_EOL;
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
