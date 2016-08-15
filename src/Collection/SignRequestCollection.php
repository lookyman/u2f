<?php
declare(strict_types=1);

namespace Lookyman\U2F\Collection;

use Lookyman\U2F\Exception\IException;
use Lookyman\U2F\Exception\SignRequestException;
use Lookyman\U2F\Request\SignRequest;
use Lookyman\U2F\Response\SignResponse;

class SignRequestCollection implements \Countable, \IteratorAggregate, \JsonSerializable
{
	/**
	 * @var SignRequest[]
	 */
	private $requests = [];

	public function add(SignRequest $request)
	{
		$this->requests[] = $request;
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
