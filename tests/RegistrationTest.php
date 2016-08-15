<?php
declare(strict_types=1);

namespace Lookyman\U2F\Tests;

use Lookyman\U2F\Helpers;
use Lookyman\U2F\Registration;

class RegistrationTest extends \PHPUnit_Framework_TestCase
{
	public function testGet()
	{
		$registration = new Registration(getPublicKey(), 'a', 'b');
		self::assertSame(getPublicKey(), $registration->getPublicKey());
		self::assertSame('a', $registration->getKeyHandle());
		self::assertSame('b', $registration->getCertificate());
		self::assertSame(-1, $registration->getCounter());

		$withoutCert = new Registration(getPublicKey(), 'c');
		self::assertSame(getPublicKey(), $withoutCert->getPublicKey());
		self::assertSame('c', $withoutCert->getKeyHandle());
		self::assertNull($withoutCert->getCertificate());
		self::assertSame(-1, $withoutCert->getCounter());
	}

	public function testSetCounter()
	{
		$registration = new Registration(getPublicKey(), 'a', 'b');
		$registration->setCounter(3);
		self::assertSame(3, $registration->getCounter());
	}

	/**
	 * @dataProvider invalidPublicKeyExceptionProvider
	 * @expectedException \Lookyman\U2F\Exception\PublicKeyException
	 * @expectedExceptionCode \Lookyman\U2F\Exception\IException::ERR_PUBKEY_DECODE
	 */
	public function testInvalidPublicKey($data)
	{
		new Registration($data, 'a', 'b');
	}

	/**
	 * @return array
	 */
	public function invalidPublicKeyExceptionProvider(): array
	{
		return [
			[''],
			[str_repeat('a', Helpers::PUBLIC_KEY_LENGTH)],
		];
	}
}
