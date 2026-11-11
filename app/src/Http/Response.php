<?php

declare(strict_types=1);

namespace YTT\Http;

final class Response
{
	/** Kept as in the old API. These are compared literally, so the wildcard ones never match (part two). */
	private const array ORIGINS = [
		'https://*.mrcraftcod.fr',
		'https://*.rakambda.fr',
		'chrome-extension://moboafdnejnjnppicfiadaalobjeemec',
		'chrome-extension://knnlnielflnfhdohmihofhdelgahgjdb/*',
		'moz-extension://5d0b45c3-3111-4e59-a44f-e6f2c818eba1/*',
	];

	/** @param array<array-key, mixed> $body */
	public static function json(int $status, array $body): never
	{
		http_response_code($status);
		header('Content-type:application/json');
		header('Access-Control-Allow-Methods:POST,PUT,GET,DELETE,OPTIONS');
		$origin = $_SERVER['HTTP_ORIGIN'] ?? null;
		if (in_array($origin, self::ORIGINS, true)) {
			header("Access-Control-Allow-Origin: $origin");
		}
		echo json_encode($body);
		exit;
	}
}
