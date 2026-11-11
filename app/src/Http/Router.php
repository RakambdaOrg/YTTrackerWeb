<?php

declare(strict_types=1);

namespace YTT\Http;

use Closure;
use Throwable;

final class Router
{
	/** @var list<array{string, string, Closure(array<int, string>, array<array-key, mixed>): array<array-key, mixed>}> */
	private array $routes = [];

	/**
	 * @param string $regex matched against the route; its groups are passed to the handler
	 * @param Closure(array<int, string>, array<array-key, mixed>): array<array-key, mixed> $handler
	 */
	public function add(string $method, string $regex, Closure $handler): self
	{
		$this->routes[] = [$method, $regex, $handler];
		return $this;
	}

	/**
	 * The first matching route wins. The HTTP status is the result's `code`, or 200.
	 *
	 * @return array{int, array<array-key, mixed>}
	 */
	public function dispatch(Request $request): array
	{
		if ($request->route === null) {
			return [404, ['code' => 404, 'result' => '']];
		}
		foreach ($this->routes as [$method, $regex, $handler]) {
			if ($method === $request->method && preg_match($regex, $request->route, $groups)) {
				try {
					$result = $handler($groups, $request->params);
				} catch (Throwable $e) {
					error_log((string) $e);
					return [500, ['code' => 500, 'message' => 'Failed to execute route']];
				}
				return [(int) ($result['code'] ?? 200), $result];
			}
		}
		return [404, ['code' => 404, 'error' => 'No route found']];
	}
}
