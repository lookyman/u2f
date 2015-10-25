<?php

namespace lookyman\U2f\Tests\Server;

use lookyman\U2f\Server\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{

	public function testGetSet()
	{
		$config = new Config('a');
		$this->assertSame('a', $config->getAppId());
		$this->assertSame(Config::U2F_VERSION, $config->getVersion());
		$this->assertNull($config->getAttestDir());

		$configWithAttestDir = new Config('a', 'b');
		$this->assertSame('a', $configWithAttestDir->getAppId());
		$this->assertSame(Config::U2F_VERSION, $configWithAttestDir->getVersion());
		$this->assertSame('b', $configWithAttestDir->getAttestDir());
	}

}
