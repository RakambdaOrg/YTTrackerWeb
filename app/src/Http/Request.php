<?php

declare(strict_types=1);

namespace YTT\Http;

final readonly class Request
{
	/**
	 * @param ?string $route the path after /api/v2/, set by .htaccess as ?request=
	 * @param array<array-key, mixed> $params
	 */
	public function __construct(
		public string $method,
		public ?string $route,
		public array $params,
	) {
	}

	/**
	 * Params are merged like the old API: JSON body, then request headers, then $_POST or $_GET (later wins).
	 *
	 * @param ?array<string, string> $headers defaults to getallheaders(), which the CLI doesn't have
	 */
	public static function fromGlobals(?string $body = null, ?array $headers = null): self
	{
		$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
		$json = json_decode($body ?? (string) file_get_contents('php://input'), true);
		$params = array_merge(
			is_array($json) ? $json : [],
			$headers ?? getallheaders(),
			match ($method) {
				'POST' => $_POST,
				'GET' => $_GET,
				default => [],
			},
		);
		$route = $_REQUEST['request'] ?? null;
		return new self($method, is_string($route) ? $route : null, $params);
	}
}
