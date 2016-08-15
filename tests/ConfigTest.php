<?php
declare(strict_types=1);

namespace Lookyman\U2F\Tests;

use Lookyman\U2F\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
	public function testGetSet()
	{
		$config = new Config('a');
		self::assertSame('a', $config->getAppId());
		self::assertSame(Config::U2F_VERSION, $config->getVersion());
		self::assertNull($config->getAttestDir());

		$configWithAttestDir = new Config('a', 'b');
		self::assertSame('a', $configWithAttestDir->getAppId());
		self::assertSame(Config::U2F_VERSION, $configWithAttestDir->getVersion());
		self::assertSame('b', $configWithAttestDir->getAttestDir());
	}
}
