<?php

declare(strict_types=1);

use YTT\Http\Request;
use YTT\Http\Router;
use YTT\StatType;

// StatType
check(StatType::fromParam('watched') === StatType::Watched, 'watched by name');
check(StatType::fromParam('1') === StatType::Watched, 'watched by string number');
check(StatType::fromParam(2) === StatType::Opened, 'opened by int');
check(StatType::fromParam('opened') === StatType::Opened, 'opened by name');
check(StatType::fromParam('3') === null, 'unknown type');
check(StatType::fromParam(['opened']) === null, 'array type');

// Request: JSON body < headers < $_GET (later wins), route from $_REQUEST
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET = ['range' => '7', 'get' => 'g'];
$_POST = ['post' => 'p'];
$_REQUEST = ['request' => 'users'];
$request = Request::fromGlobals('{"range": 1, "body": "b", "Host": "body"}', ['Host' => 'header', 'range' => '3']);
check($request->route === 'users', 'route');
check($request->params['range'] === '7', 'GET overrides header and body');
check($request->params['Host'] === 'header', 'header overrides body');
check($request->params['body'] === 'b' && $request->params['get'] === 'g', 'merged keys');
check(!isset($request->params['post']), 'no POST params on GET');

$_SERVER['REQUEST_METHOD'] = 'POST';
$request = Request::fromGlobals('not json', []);
check($request->params === ['post' => 'p'], 'POST params, invalid JSON ignored');

$_REQUEST = ['request' => ['users']];
check(Request::fromGlobals('', [])->route === null, 'non-string route');

// Router
$router = new Router()
	->add('GET', '/(\d+)\/a$/', static fn(array $groups, array $params) => ['id' => $groups[1], 'p' => $params['p'] ?? null])
	->add('GET', '/b$/', static fn() => ['code' => 204, 'error' => 'none'])
	->add('GET', '/c$/', static fn() => throw new RuntimeException('test exception, expected in the log'));

check($router->dispatch(new Request('GET', '12/a', ['p' => 'x'])) === [200, ['id' => '12', 'p' => 'x']], 'match, groups and params');
check($router->dispatch(new Request('GET', 'b', [])) === [204, ['code' => 204, 'error' => 'none']], 'status from code');
check($router->dispatch(new Request('POST', '12/a', [])) === [404, ['code' => 404, 'error' => 'No route found']], 'method must match');
check($router->dispatch(new Request('GET', null, [])) === [404, ['code' => 404, 'result' => '']], 'no route param');
check($router->dispatch(new Request('GET', 'x/12/a', []))[0] === 200, 'unanchored start, like the old regexes');
ini_set('error_log', '/dev/null');
check($router->dispatch(new Request('GET', 'c', [])) === [500, ['code' => 500, 'message' => 'Failed to execute route']], 'exception gives 500');
