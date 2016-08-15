<?php
declare(strict_types=1);

namespace Lookyman\U2F\Collection;

use Lookyman\U2F\Exception\IException;
use Lookyman\U2F\Exception\RegistrationException;
use Lookyman\U2F\Registration;
use Lookyman\U2F\Response\SignResponse;

class RegistrationCollection implements \Countable, \IteratorAggregate
{
	/**
	 * @var Registration[]
	 */
	private $registrations = [];

	/**
	 * @param Registration $registration
	 */
	public function add(Registration $registration)
	{
		$this->registrations[] = $registration;
	}

	/**
	 * @return Registration
	 */
	public function getMatchingRegistration(SignResponse $signResponse): Registration
	{
		foreach ($this->registrations as $registration) {
			if ($registration->getKeyHandle() === $signResponse->getKeyHandle()) {
				return $registration;
			}
		}
		throw new RegistrationException('No matching registration found', IException::ERR_NO_MATCHING_REGISTRATION);
	}

	/**
	 * @internal
	 * @return \Traversable
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->registrations);
	}

	/**
	 * @internal
	 * @return int
	 */
	public function count()
	{
		return count($this->registrations);
	}
}
