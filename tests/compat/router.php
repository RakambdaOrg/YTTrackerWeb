<?php

// php -S router doing what app/.htaccess does for the v2 API. Run from the app root to serve.
if (preg_match('#^/api/v2/(.*)$#', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), $m) && !is_file($_SERVER['DOCUMENT_ROOT'] . $m[0])) {
	$_GET['request'] = $_REQUEST['request'] = $m[1];
	require $_SERVER['DOCUMENT_ROOT'] . '/api/v2/index.php';
	return true;
}
return false;
