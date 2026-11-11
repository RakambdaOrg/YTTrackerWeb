<?php

// Plain-PHP checks, no framework: php tests/run.php

declare(strict_types=1);

require __DIR__ . '/../app/src/bootstrap.php';


$failures = 0;
function check(bool $ok, string $what): void
{
	global $failures;
	if (!$ok) {
		$failures++;
		fwrite(STDERR, "FAIL: $what\n");
	}
}

foreach (glob(__DIR__ . '/*Test.php') as $file) {
	require $file;
}

echo $failures === 0 ? "OK\n" : "$failures failure(s)\n";
exit($failures === 0 ? 0 : 1);
