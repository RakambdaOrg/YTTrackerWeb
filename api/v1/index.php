<?php
require_once('model/DBConnection.class.php');
require_once('model/DBHandler.class.php');

$http_origin = $_SERVER['HTTP_ORIGIN'];
if (!($http_origin == "http://*.mrcraftcod.fr" || $http_origin == "chrome-extension://moboafdnejnjnppicfiadaalobjeemec" || $http_origin == "chrome-extension://knnlnielflnfhdohmihofhdelgahgjdb/*"))
{
    header('HTTP/1.0 403 Forbidden');
    exit;
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        processPut($_REQUEST);
        break;
    case 'GET':
        processGet($_REQUEST);
        break;
    default:
        sendResponse(501);
        break;
}

function processPut($params)
{
    if (!isset($params['request']))
        sendResponse(404);
    switch ($params['request']) {
        case 'stats/add':
            if (!isset($params['stats']) || !isset($params['uuid']) || !isset($params['type']) || !isset($params['videoID'])) {
                sendResponse(400);
                return;
            }
            processRequest('addStats', $params);
            break;
        case 'usernames/set':
            if (!isset($params['uuid']) || !isset($params['username'])) {
                sendResponse(400);
                return;
            }
            processRequest('setUsername', $params);
            break;

        default:
            sendResponse(404);
    }
}

function processGet($params)
{
    if (!isset($params['request']))
        sendResponse(404);
    switch ($params['request']) {
        case 'stats/get':
            if (!isset($params['uuid'])) {
                sendResponse(400);
                return;
            }
            processRequest('getStats', $params);
            break;

        default:
            sendResponse(404);
    }
}

function sendResponse($status = 200, $body = '')
{
    header('HTTP/1.1 ' . $status . ' ' . getStatusCodeMessage($status));
    header('Content-type:' . 'application/json');
    header('Access-Control-Allow-Methods:' . 'POST,PUT,GET,DELETE,OPTIONS');
    $http_origin = $_SERVER['HTTP_ORIGIN'];
    if ($http_origin == "http://*.mrcraftcod.fr" || $http_origin == "chrome-extension://moboafdnejnjnppicfiadaalobjeemec" || $http_origin == "chrome-extension://knnlnielflnfhdohmihofhdelgahgjdb/*")
    {
        header("Access-Control-Allow-Origin: $http_origin");
    }
    if ($body != '') {
        echo $body;
        exit;
    } else {
        echo json_encode(array('code' => $status, 'result' => ''));
        exit;
    }
}

function getStatusCodeMessage($status)
{
    $codes = Array(
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

function processRequest($methodName, $params)
{
    $conn = DBConnection::getConnection();
    if ($conn->connect_error) {
        sendResponse(500, json_encode(array('code' => '500', 'result' => 'err', 'error'=>'E0')));
        return;
    }
    if (!$methodName($conn, $params)) {
        sendResponse(500, json_encode(array('code' => '500')));
    }
    $conn->close();
}

function addStats($conn, $params)
{
    $dbHandler = new DBhandler();
    $result = $dbHandler->addStat($conn, $params['uuid'], $params['type'], $params['stats'], $params['videoID']);
    sendResponse($result['code'], json_encode($result));
    return true;
}

function getStats($conn, $params)
{
    $dbHandler = new DBhandler();
    $result = $dbHandler->getStats($conn, $params['uuid']);
    sendResponse($result['code'], json_encode($result));
    return true;
}

function setUsername($conn, $params)
{
    $dbHandler = new DBhandler();
    $result = $dbHandler->setUsername($conn, $params['uuid'], $params['username']);
    sendResponse($result['code'], json_encode($result));
    return true;
}