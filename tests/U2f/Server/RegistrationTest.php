<?php

namespace lookyman\U2f\Tests\Server;

use lookyman\U2f\Server\Helpers;
use lookyman\U2f\Server\Registration;

class RegistrationTest extends \PHPUnit_Framework_TestCase
{

	public function testGet()
	{
		$registration = new Registration($this->getPublicKey(), 'a', 'b');
		$this->assertSame($this->getPublicKey(), $registration->getPublicKey());
		$this->assertSame('a', $registration->getKeyHandle());
		$this->assertSame('b', $registration->getCertificate());
		$this->assertSame(-1, $registration->getCounter());
	}

	public function testSetCounter()
	{
		$registration = new Registration($this->getPublicKey(), 'a', 'b');
		$registration->setCounter(3);
		$this->assertSame(3, $registration->getCounter());
	}

	/**
	 * @dataProvider invalidPublicKeyExceptionProvider
	 * @expectedException \lookyman\U2f\Exception\PublicKeyException
	 * @expectedExceptionCode \lookyman\U2f\Exception\IException::ERR_PUBKEY_DECODE
	 */
	public function testInvalidPublicKey($data)
	{
		new Registration($data, 'a', 'b');
	}

	public function invalidPublicKeyExceptionProvider()
	{
		return [
			[''],
			[str_repeat('a', Helpers::PUBLIC_KEY_LENGTH)],
		];
	}

	private function getPublicKey()
	{
		return "\x04" . str_repeat("\0", Helpers::PUBLIC_KEY_LENGTH - 1);
	}

}
