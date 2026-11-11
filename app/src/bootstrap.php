<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
	if (str_starts_with($class, 'YTT\\')) {
		require __DIR__ . '/' . str_replace('\\', '/', substr($class, 4)) . '.php';
	}
});
