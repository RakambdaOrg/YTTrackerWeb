<?php

	require_once __DIR__ . '/model/DBConnection.class.php';

	use YTT\DBConnection;

	if(false)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', '1');
	}

	require_once __DIR__ . '/model/DBConnection.class.php';
	require_once __DIR__ . '/model/StatsHandler.class.php';
	require_once __DIR__ . '/model/UsersHandler.class.php';

	$uuidRegex = "([0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12})";
	$categoryRegex = "([A-Za-z]+)";

	$statsHandler = new YTT\StatsHandler();
	$usersHandler = new YTT\UsersHandler();

	$endpoints = array();

	$endpoints[] = array('method' => 'GET', 'route' => "/$uuidRegex\/stats$/", 'object' => $statsHandler, 'function' => 'getUserStats');
	$endpoints[] = array('method' => 'GET', 'route' => "/$uuidRegex\/stats\/watched$/", 'object' => $statsHandler, 'function' => 'getUserWatched');
	$endpoints[] = array('method' => 'GET', 'route' => "/$uuidRegex\/stats\/opened$/", 'object' => $statsHandler, 'function' => 'getUserOpened');
	$endpoints[] = array('method' => 'GET', 'route' => "/$uuidRegex\/stats\/opened-count$/", 'object' => $statsHandler, 'function' => 'getUserOpenedCount');
	$endpoints[] = array('method' => 'POST', 'route' => "/$uuidRegex\/stats\/add$/", 'object' => $statsHandler, 'function' => 'addUserStat');

	$endpoints[] = array('method' => 'GET', 'route' => "/users$/", 'object' => $usersHandler, 'function' => 'getUsers');
	$endpoints[] = array('method' => 'GET', 'route' => "/$uuidRegex\/username$/", 'object' => $usersHandler, 'function' => 'getUsername');
	$endpoints[] = array('method' => 'POST', 'route' => "/$uuidRegex\/username$/", 'object' => $usersHandler, 'function' => 'setUserUsername');

	if(!isset($_REQUEST['request']))
		sendResponse(404);

	$params = json_decode(file_get_contents('php://input'), true);
	if($params === null)
		$params = array();
	$params = array_merge($params, apache_request_headers());
	switch($_SERVER['REQUEST_METHOD'])
	{
		case 'POST':
			$params = array_merge($params, $_POST);
			break;
		case 'GET':
			$params = array_merge($params, $_GET);
			break;
	}
	processHttpRequest($endpoints, $_SERVER['REQUEST_METHOD'], $_REQUEST['request'], $params);

	/**
	 * @param array $endpoints
	 * @param string $method
	 * @param string $route
	 * @param array $params
	 */
	function processHttpRequest($endpoints, $method, $route, $params)
	{
		foreach($endpoints as $endpointIndex => $endpoint)
		{
			$groups = array();
			if($endpoint['method'] === $method && preg_match($endpoint['route'], $route, $groups))
			{
				if(isset($endpoint['authRequired']) && $endpoint['authRequired'])
				{
					if(!checkAuth())
					{
						sendResponse(403);
						return;
					}
				}

				$result = $endpoint['object']->{$endpoint['function']}($groups, $params);
				if($result !== false)
				{
					$code = 200;
					if(isset($result['code']))
					{
						$code = $result['code'];
					}
					sendResponse($code, json_encode($result));
					return;
				}
				else
				{
					sendResponse(500, json_encode(array('code' => 500, 'message' => 'Failed to execute route')));
					return;
				}
			}
		}

		sendResponse(404, json_encode(array('code' => 404, "error" => 'No route found')));
	}

	/**
	 * @return bool
	 */
	function checkAuth()
	{
		if(isset($_SERVER['HTTP_AUTHORIZATION']))
		{
			$auth = $_SERVER['HTTP_AUTHORIZATION'];
			$parts = explode(' ', $auth);
			if(count($parts) === 2)
				switch($parts[0])
				{
					case 'Bearer':
						return true;
					default:
						break;
				}
		}
		return false;
	}

	/**
	 * @param int $status
	 * @param string $body
	 */
	function sendResponse($status = 200, $body = '')
	{
		DBConnection::close();
		header('HTTP/1.1 ' . $status . ' ' . getStatusCodeMessage($status));
		header('Content-type:' . 'application/json');
		header('Access-Control-Allow-Methods:' . 'POST,PUT,GET,DELETE,OPTIONS');
		$http_origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : null;
		if($http_origin !== null && ($http_origin === "http://*.mrcraftcod.fr" || $http_origin === "chrome-extension://moboafdnejnjnppicfiadaalobjeemec" || $http_origin === "chrome-extension://knnlnielflnfhdohmihofhdelgahgjdb/*"))
		{
			header("Access-Control-Allow-Origin: $http_origin");
		}
		if($body != '')
		{
			echo $body;
			exit;
		}
		else
		{
			echo json_encode(array('code' => $status, 'result' => ''));
			exit;
		}
	}

	/**
	 * @param int $status
	 * @return string
	 */
	function getStatusCodeMessage($status)
	{
		$codes = array(
			100 => 'Continue',
			101 => 'Switching Protocols',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			306 => '(Unused)',
			307 => 'Temporary Redirect',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported'
		);
		return (isset($codes[$status])) ? $codes[$status] : '';
	}
