<?php
declare(strict_types=1);

namespace Lookyman\U2F\Request;

use Lookyman\U2F\Collection\SignRequestCollection;
use Lookyman\U2F\Helpers;

class RegisterRequest implements \JsonSerializable
{
	/**
	 * @var string
	 */
	private $version;

	/**
	 * @var string
	 */
	private $appId;

	/**
	 * @var string
	 */
	private $challenge;

	/**
	 * @var SignRequestCollection
	 */
	private $signRequests;

	public function __construct(string $version, string $appId, string $challenge, SignRequestCollection $signRequests)
	{
		$this->version = $version;
		$this->appId = $appId;
		$this->challenge = $challenge;
		$this->signRequests = $signRequests;
	}

	/**
	 * @return string
	 */
	public function getAppId(): string
	{
		return $this->appId;
	}

	/**
	 * @return string
	 */
	public function getChallenge(): string
	{
		return $this->challenge;
	}

	/**
	 * @return SignRequestCollection
	 */
	public function getSignRequests(): SignRequestCollection
	{
		return $this->signRequests;
	}

	/**
	 * @return string
	 */
	public function getVersion(): string
	{
		return $this->version;
	}

	/**
	 * @internal
	 * @return array
	 */
	public function jsonSerialize()
	{
		return [
			'version' => $this->version,
			'challenge' => Helpers::urlSafeBase64Encode($this->challenge),
			'appId' => $this->appId,
		];
	}
}
