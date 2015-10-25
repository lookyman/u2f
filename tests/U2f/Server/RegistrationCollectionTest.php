<?php

namespace lookyman\U2f\Tests\Server;

use lookyman\U2f\Server\Helpers;
use lookyman\U2f\Server\Registration;
use lookyman\U2f\Server\RegistrationCollection;
use lookyman\U2f\Server\SignResponse;

class RegistrationCollectionTest extends \PHPUnit_Framework_TestCase
{

	public function testGetSet()
	{
		$registrations = new RegistrationCollection;
		$registrations->add(new Registration($this->getPublicKey(), 'a', 'b'));
		$registrations->add(new Registration($this->getPublicKey(), 'c', 'd'));
		$this->assertCount(2, $registrations);
		$this->assertSame(2, count($registrations));
	}

	public function testGetMatchingRegistration()
	{
		$registrations = new RegistrationCollection;
		$registrations->add($registration = new Registration($this->getPublicKey(), 'a', 'b'));

		$this->assertSame($registration, $registrations->getMatchingRegistration(new SignResponse('a', 'abcdef', '{"challenge":"c"}')));
	}

	/**
	 * @expectedException \lookyman\U2f\Exception\RegistrationException
	 * @expectedExceptionCode \lookyman\U2f\Exception\IException::ERR_NO_MATCHING_REGISTRATION
	 */
	public function testNoMatchingRegistration()
	{
		$registrations = new RegistrationCollection;
		$registrations->add($registration = new Registration($this->getPublicKey(), 'a', 'b'));

		$registrations->getMatchingRegistration(new SignResponse('g', 'abcdef', '{"challenge":"c"}'));
	}

	private function getPublicKey()
	{
		return "\x04" . str_repeat("\0", Helpers::PUBLIC_KEY_LENGTH - 1);
	}

}
