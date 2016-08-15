<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

function getPublicKey(): string
{
	return "\x04" . str_repeat("\0", \Lookyman\U2F\Helpers::PUBLIC_KEY_LENGTH - 1);
}
