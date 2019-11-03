<?php

/*
Plugin Name: Schnell
Description: Improves the speed of your site by enabling client side caching with "expire" headers, "cache-control" headers &amp; output compression. Why this plugin name? Schnell means "quick" in German.
Plugin URI: https://github.com/lutrov/schnell
Author: Ivan Lutrov
Author URI: http://lutrov.com/
Version: 4.0
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
	$result .= '# Response headers' . PHP_EOL;
	$result .= '<IfModule mod_headers.c>' . PHP_EOL;
	$result .= "\t" . 'Header set Cache-Control "public"' . PHP_EOL;
	$result .= "\t" . 'Header set Vary "Accept-Encoding"' . PHP_EOL;
	$result .= "\t" . 'Header set X-Content-Type-Options "nosniff"' . PHP_EOL;
	$result .= "\t" . 'Header unset X-Frame-Options' . PHP_EOL;
	$result .= "\t" . 'Header unset X-Pingback' . PHP_EOL;
	$result .= "\t" . 'Header unset X-Powered-By' . PHP_EOL;
	$result .= "\t" . 'Header unset ETag' . PHP_EOL;
	$result .= '</IfModule>' . PHP_EOL;
	$result .= '# Leverage browser caching' . PHP_EOL;
	$result .= '<IfModule mod_expires.c>' . PHP_EOL;
	$result .= "\t" . 'ExpiresActive On' . PHP_EOL;
	$result .= "\t" . 'ExpiresDefault A' . HOUR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . '# Data files are not cached' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType application/json A0' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType application/xml A0' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType text/html A0' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType text/plain A0' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType text/xml A0' . PHP_EOL;
	$result .= "\t" . '# Feeds are cached for 1 hour' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType application/atom+xml A' . HOUR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType application/rss+xml A' . HOUR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType text/x-component A' . HOUR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . '# Scripts are cached for 1 month' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType application/javascript A' . MONTH_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType application/x-javascript A' . MONTH_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType text/css A' . MONTH_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType text/javascript A' . MONTH_IN_SECONDS . PHP_EOL;
	$result .= "\t" . '# Images are cached for 1 year' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType image/bmp A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType image/gif A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType image/jp2 A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType image/jpe A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType image/jpeg A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType image/jpg A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType image/pipeg A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType image/png A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType image/svg+xml A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType image/tiff A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType image/vnd.microsoft.icon A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . '# Icons are cached for 1 year' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType application/ico A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType image/ico A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType image/icon A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType image/x-ico A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType image/x-icon A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType text/ico A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . '# Audio files are cached for 1 year' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType audio/basic A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType audio/mid A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType audio/midi A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType audio/mpeg A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType audio/ogg A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType audio/x-aiff A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType audio/x-mpegurl A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType audio/x-pn-realaudio A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType audio/x-wav A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . '# Video files are cached for 1 year' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType video/mp4 A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType video/mpeg A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType video/ogg A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType video/quicktime A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType video/webm A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType video/x-la-asf A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType video/x-ms-asf A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType video/x-msvideo A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType x-world/x-vrml A' . YEAR_IN_SECONDS . PHP_EOL;
	$result .= "\t" . '# Fonts are cached for 1 month' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType application/font-woff A' . MONTH_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType application/vnd.ms-fontobject A' . MONTH_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType application/x-font-ttf A' . MONTH_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType application/x-font-woff A' . MONTH_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType font/opentype A' . MONTH_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType font/truetype A' . MONTH_IN_SECONDS . PHP_EOL;
	$result .= "\t" . '# Other files are cached for 1 month' . PHP_EOL;
	$result .= "\t" . 'ExpiresByType application/pdf A' . MONTH_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType application/smil A' . MONTH_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType application/vnd.wap.wbxml A' . MONTH_IN_SECONDS . PHP_EOL;
	$result .= "\t" . 'ExpiresByType image/vnd.wap.wbmp A' . MONTH_IN_SECONDS . PHP_EOL;
	$result .= '</IfModule>' . PHP_EOL;
	$result .= '# Enable compression' . PHP_EOL;
	$result .= '<IfModule mod_deflate.c>' . PHP_EOL;
	$result .= "\t" . 'AddOutputFilterByType DEFLATE application/javascript' . PHP_EOL;
	$result .= "\t" . 'AddOutputFilterByType DEFLATE application/rss+xml' . PHP_EOL;
	$result .= "\t" . 'AddOutputFilterByType DEFLATE application/vnd.ms-fontobject' . PHP_EOL;
	$result .= "\t" . 'AddOutputFilterByType DEFLATE application/x-font' . PHP_EOL;
	$result .= "\t" . 'AddOutputFilterByType DEFLATE application/x-font-opentype' . PHP_EOL;
	$result .= "\t" . 'AddOutputFilterByType DEFLATE application/x-font-otf' . PHP_EOL;
	$result .= "\t" . 'AddOutputFilterByType DEFLATE application/x-font-truetype' . PHP_EOL;
	$result .= "\t" . 'AddOutputFilterByType DEFLATE application/x-font-ttf' . PHP_EOL;
	$result .= "\t" . 'AddOutputFilterByType DEFLATE application/x-javascript' . PHP_EOL;
	$result .= "\t" . 'AddOutputFilterByType DEFLATE application/xhtml+xml' . PHP_EOL;
	$result .= "\t" . 'AddOutputFilterByType DEFLATE application/xml' . PHP_EOL;
	$result .= "\t" . 'AddOutputFilterByType DEFLATE font/opentype' . PHP_EOL;
	$result .= "\t" . 'AddOutputFilterByType DEFLATE font/otf' . PHP_EOL;
	$result .= "\t" . 'AddOutputFilterByType DEFLATE font/ttf' . PHP_EOL;
	$result .= "\t" . 'AddOutputFilterByType DEFLATE image/svg+xml' . PHP_EOL;
	$result .= "\t" . 'AddOutputFilterByType DEFLATE image/x-icon' . PHP_EOL;
	$result .= "\t" . 'AddOutputFilterByType DEFLATE text/css' . PHP_EOL;
	$result .= "\t" . 'AddOutputFilterByType DEFLATE text/html' . PHP_EOL;
	$result .= "\t" . 'AddOutputFilterByType DEFLATE text/javascript' . PHP_EOL;
	$result .= "\t" . 'AddOutputFilterByType DEFLATE text/plain' . PHP_EOL;
	$result .= "\t" . 'AddOutputFilterByType DEFLATE text/xml' . PHP_EOL;
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
