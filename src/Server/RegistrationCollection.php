<?php

namespace lookyman\U2f\Server;

use lookyman\U2f\Exception\IException;
use lookyman\U2f\Exception\RegistrationException;

class RegistrationCollection implements \Countable, \IteratorAggregate
{

	/** @var Registration[] */
	private $registrations = [];

	/**
	 * @return self
	 */
	public function add(Registration $registration)
	{
		$this->registrations[] = $registration;
		return $this;
	}

	/**
	 * @return Registration
	 */
	public function getMatchingRegistration(SignResponse $response)
	{
		foreach ($this->registrations as $registration) {
			if ($registration->getKeyHandle() === $response->getKeyHandle()) {
				return $registration;
			}
		}
		throw new RegistrationException('No matching Registration found.', IException::ERR_NO_MATCHING_REGISTRATION);
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
