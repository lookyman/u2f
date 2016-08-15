<?php
declare(strict_types=1);

namespace Lookyman\U2F\Tests\Collection;

use Lookyman\U2F\Collection\RegistrationCollection;
use Lookyman\U2F\Registration;
use Lookyman\U2F\Response\SignResponse;

class RegistrationCollectionTest extends \PHPUnit_Framework_TestCase
{
	public function testGetSet()
	{
		$registrations = new RegistrationCollection();
		$registrations->add(new Registration(getPublicKey(), 'a', 'b'));
		$registrations->add(new Registration(getPublicKey(), 'c', 'd'));
		self::assertCount(2, $registrations);
		self::assertSame(2, count($registrations));
	}

	public function testGetMatchingRegistration()
	{
		$registrations = new RegistrationCollection();
		$registrations->add($registration = new Registration(getPublicKey(), 'a', 'b'));

		self::assertSame($registration, $registrations->getMatchingRegistration(new SignResponse('a', 'abcdef', '{"challenge":"c"}')));
	}

	/**
	 * @expectedException \Lookyman\U2F\Exception\RegistrationException
	 * @expectedExceptionCode \Lookyman\U2F\Exception\IException::ERR_NO_MATCHING_REGISTRATION
	 */
	public function testNoMatchingRegistration()
	{
		$registrations = new RegistrationCollection();
		$registrations->add($registration = new Registration(getPublicKey(), 'a', 'b'));

		$registrations->getMatchingRegistration(new SignResponse('g', 'abcdef', '{"challenge":"c"}'));
	}
}
