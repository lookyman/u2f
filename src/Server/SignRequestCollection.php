<?php

namespace lookyman\U2f\Server;

use lookyman\U2f\Exception\IException;
use lookyman\U2f\Exception\SignRequestException;

class SignRequestCollection implements \Countable, \IteratorAggregate, \JsonSerializable
{

	/** @var SignRequest[] */
	private $requests = [];

	/**
	 * @return self
	 */
	public function add(SignRequest $request)
	{
		$this->requests[] = $request;
		return $this;
	}

	/**
	 * @return SignRequest
	 */
	public function getMatchingRequest(SignResponse $response)
	{
		foreach ($this->requests as $request) {
			if ($request->getKeyHandle() === $response->getKeyHandle() && $request->getChallenge() === $response->getChallenge()) {
				return $request;
			}
		}
		throw new SignRequestException('No matching request found.', IException::ERR_NO_MATCHING_REQUEST);
	}

	/**
	 * @internal
	 * @return \Traversable
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->requests);
	}

	/**
	 * @internal
	 * @return int
	 */
	public function count()
	{
		return count($this->requests);
	}

	/**
	 * @internal
	 * @return SignRequest[]
	 */
	public function jsonSerialize()
	{
		return $this->requests;
	}

}
