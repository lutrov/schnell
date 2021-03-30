<?php

/*
Plugin Name: Schnell
Version: 5.0
Description: Improves the speed of your site by enabling client side caching with `Expires` headers, `Cache-Control` headers &amp; output compression. Why this plugin name? Schnell means "quick" in German.
Plugin URI: https://github.com/lutrov/schnell
Copyright: 2018, Ivan Lutrov
Author: Ivan Lutrov
Author URI: http://lutrov.com/

This program is free software; you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation; either version 2 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program; if not, write to the Free Software Foundation, Inc., 51 Franklin
Street, Fifth Floor, Boston, MA 02110-1301, USA. Also add information on how to
contact you by electronic and paper mail.
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
			$content = preg_replace('/# BEGIN Schnell Cache(.+)# END Schnell Cache/isU', $hash, $content, -1, $count);
			switch ($action) {
				case 'install':
					if ($count > 0) {
						$content = str_replace($hash, $rules, $content);
					} else {
						$content = sprintf('%s%s%s', $rules, PHP_EOL, trim($content));
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
// Build .htaccess expiry & compression rules.
// Cache expiry is 1 year for images/video, 1 month for JS/fonts/PDF, 1 week for CSS.
//
function schnell_htaccess_rules() {
	$result  = '# BEGIN Schnell Cache' . PHP_EOL;
	$result .= '# Browser caching using Expires headers' . PHP_EOL;
	$result .= '<IfModule mod_expires.c>' . PHP_EOL;
	$result .= "\t" . 'ExpiresActive On' . PHP_EOL;
	$result .= "\t" . '# One week for stylesheet files' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType text/css "access plus 1 week"' . PHP_EOL;
	$result .= "\t" . '# One month for script files' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType text/javascript "access plus 1 month"' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType application/javascript "access plus 1 month"' . PHP_EOL;
	$result .= "\t" . '# One month for font files' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType font/woff "access plus 1 month"' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType font/woff2 "access plus 1 month"' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType font/ttf "access plus 1 month"' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType font/otf "access plus 1 month"' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType image/svg+xml "access plus 1 month"' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType application/vnd.ms-fontobject "access plus 1 month"' . PHP_EOL;
	$result .= "\t" . '# One month for portable document files' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType application/pdf "access plus 1 month"' . PHP_EOL;
	$result .= "\t" . '# One year for image files' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType image/jpeg "access plus 1 year"' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType image/png "access plus 1 year"' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType image/webp "access plus 1 year"' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType image/gif "access plus 1 year"' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType image/svg+xml "access plus 1 year"' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType image/x-icon "access plus 1 year"' . PHP_EOL;
	$result .= "\t" . '# One year for video files' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType video/mp4 "access plus 1 year"' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType video/mpeg "access plus 1 year"' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType video/x-matroska "access plus 1 year"' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType video/avi "access plus 1 year"' . PHP_EOL;
	$result .= "\t" . '# Others' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType application/x-shockwave-flash "access plus 1 month"' . PHP_EOL;
	$result .= '</IfModule>' . PHP_EOL;
	$result .= '# Browser caching using Cache-Control headers' . PHP_EOL;
	$result .= '<ifModule mod_headers.c>' . PHP_EOL;
	$result .= "\t" . '# One week for stylesheet files' . PHP_EOL;
	$result .= "\t" . '<filesMatch ".(css)$">' . PHP_EOL;
	$result .= "\t\t" . 'Header set Cache-Control "max-age=604800, public"' . PHP_EOL;
	$result .= "\t" . '</filesMatch>' . PHP_EOL;
	$result .= "\t" . '# One month for script files' . PHP_EOL;
	$result .= "\t" . '<filesMatch ".(js)$">' . PHP_EOL;
	$result .= "\t\t" . 'Header set Cache-Control "max-age=2592000, public"' . PHP_EOL;
	$result .= "\t" . '</filesMatch>' . PHP_EOL;
	$result .= "\t" . '# One month for portable document files' . PHP_EOL;
	$result .= "\t" . '<filesMatch ".(pdf)$">' . PHP_EOL;
	$result .= "\t\t" . 'Header set Cache-Control "max-age=2592000, public"' . PHP_EOL;
	$result .= "\t" . '</filesMatch>' . PHP_EOL;
	$result .= "\t" . '# One year for image files' . PHP_EOL;
	$result .= "\t" . '<filesMatch ".(jpg|jpeg|png|webp|gif|svg|ico)$">' . PHP_EOL;
	$result .= "\t\t" . 'Header set Cache-Control "max-age=31536000, public"' . PHP_EOL;
	$result .= "\t" . '</filesMatch>' . PHP_EOL;
	$result .= "\t" . '# One year for video files' . PHP_EOL;
	$result .= "\t" . '<filesMatch ".(mp4|mpeg|mkv|avi)$">' . PHP_EOL;
	$result .= "\t\t" . 'Header set Cache-Control "max-age=31536000, public"' . PHP_EOL;
	$result .= "\t" . '</filesMatch>' . PHP_EOL;
	$result .= '</ifModule>' . PHP_EOL;
	$result .= '# Gzip compression' . PHP_EOL;
	$result .= '<IfModule mod_deflate.c>' . PHP_EOL;
	$result .= "\t" . '# Active compression' . PHP_EOL;
	$result .= "\t" . 'SetOutputFilter DEFLATE' . PHP_EOL;
	$result .= "\t" . '# Force deflate for mangled headers' . PHP_EOL;
	$result .= "\t" . '<IfModule mod_setenvif.c>' . PHP_EOL;
	$result .= "\t\t" . '<IfModule mod_headers.c>' . PHP_EOL;
	$result .= "\t\t\t" . 'SetEnvIfNoCase ^(Accept-EncodXng|X-cept-Encoding|X{15}|~{15}|-{15})$ ^((gzip|deflate)\s*,?\s*)+|[X~-]{4,13}$ HAVE_Accept-Encoding' . PHP_EOL;
	$result .= "\t\t\t" . 'RequestHeader append Accept-Encoding "gzip,deflate" env=HAVE_Accept-Encoding' . PHP_EOL;
	$result .= "\t\t\t" . '# Do not compress images and other uncompressible content' . PHP_EOL;
	$result .= "\t\t\t" . 'SetEnvIfNoCase Request_URI \.(?:gif|jpe?g|png|rar|zip|exe|flv|mov|wma|mp3|avi|swf|mp?g|mp4|webm|webp|pdf)$ no-gzip dont-vary' . PHP_EOL;
	$result .= "\t\t" . '</IfModule>' . PHP_EOL;
	$result .= "\t" . '</IfModule>' . PHP_EOL;
	$result .= "\t" . '# Compress all output labeled with one of the following MIME types' . PHP_EOL;
	$result .= "\t" . '<IfModule mod_filter.c>' . PHP_EOL;
	$result .= "\t\t" . 'AddOutputFilterByType DEFLATE application/atom+xml application/javascript application/json application/rss+xml application/vnd.ms-fontobject application/x-font-ttf application/xhtml+xml application/xml font/opentype image/svg+xml image/x-icon text/css text/html text/plain text/x-component text/xml' . PHP_EOL;
	$result .= "\t" . '</IfModule>' . PHP_EOL;
	$result .= "\t" . '<IfModule mod_headers.c>' . PHP_EOL;
	$result .= "\t\t" . 'Header append Vary: Accept-Encoding' . PHP_EOL;
	$result .= "\t" . '</IfModule>' . PHP_EOL;
	$result .= '</IfModule>' . PHP_EOL;
	$result .= '# END Schnell Cache' . PHP_EOL;
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
