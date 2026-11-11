<?php

declare(strict_types=1);

namespace YTT;

enum StatType: int
{
	case Watched = 1;
	case Opened = 2;

	/** Accepts the API's names and numbers ("watched", "1", 1...). */
	public static function fromParam(mixed $value): ?self
	{
		return is_scalar($value) ? match ((string) $value) {
			'1', 'watched' => self::Watched,
			'2', 'opened' => self::Opened,
			default => null,
		} : null;
	}
}
