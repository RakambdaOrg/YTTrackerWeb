<?php

declare(strict_types=1);

require __DIR__ . '/../../src/bootstrap.php';

use YTT\Http\Request;
use YTT\Http\Response;
use YTT\Http\Router;
use YTT\StatsRepository;
use YTT\StatType;
use YTT\UserRepository;

const UUID = '([0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12})';

$users = new UserRepository();
$stats = new StatsRepository($users);

$router = new Router()
	->add('GET', '/' . UUID . '\/stats$/', static fn(array $g) => $stats->summary($g[1]))
	->add('GET', '/' . UUID . '\/stats\/watched$/', static fn(array $g, array $p) => $stats->durationSeries($g[1], StatType::Watched, StatsRepository::range($p['range'] ?? null)))
	->add('GET', '/' . UUID . '\/stats\/opened$/', static fn(array $g, array $p) => $stats->durationSeries($g[1], StatType::Opened, StatsRepository::range($p['range'] ?? null)))
	->add('GET', '/' . UUID . '\/stats\/opened-count$/', static fn(array $g, array $p) => $stats->countSeries($g[1], StatsRepository::range($p['range'] ?? null)))
	->add('POST', '/' . UUID . '\/stats\/add$/', static fn(array $g, array $p) => match (true) {
		!isset($p['type'], $p['stat'], $p['date']) => ['code' => 400, 'message' => 'Missing fields'],
		$stats->add($g[1], $p['type'], $p['stat'], (string) $p['date']) => ['code' => 200, 'result' => 'OK'],
		default => ['code' => 400, 'result' => 'err', 'error' => 'E2.2'],
	})
	->add('GET', '/users$/', static fn(array $g, array $p) => [
		'code' => 200,
		'users' => $users->list(isset($p['range']) ? intval($p['range']) : null),
		'message' => 'OK',
	])
	->add('GET', '/' . UUID . '\/username$/', static fn(array $g) => ($name = $users->username($g[1])) === null
		? ['code' => 204, 'error' => 'UserID not found']
		: ['code' => 200, 'username' => $name])
	->add('POST', '/' . UUID . '\/username$/', static fn(array $g, array $p) => $users->setUsername($g[1], $p['username'] ?? null)
		? ['code' => 200, 'result' => 'OK']
		: ['code' => 500, 'result' => 'err', 'error' => 'E4']);

[$status, $body] = $router->dispatch(Request::fromGlobals());
Response::json($status, $body);
