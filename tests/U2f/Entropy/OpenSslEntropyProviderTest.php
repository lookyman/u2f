<?php

namespace lookyman\U2f\Tests\Entropy;

use lookyman\U2f\Entropy\OpenSslEntropyProvider;

class OpenSslEntropyProviderTest extends \PHPUnit_Framework_TestCase
{

	public function testGetPseudoRandomBytes()
	{
		$bytes = (new OpenSslEntropyProvider)->getPseudoRandomBytes(32);
		$this->assertInternalType('string', $bytes);
		$this->assertSame(32, strlen($bytes));
	}

}
