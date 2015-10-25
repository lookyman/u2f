<?php

namespace lookyman\U2f\Server;

class RegisterRequest implements \JsonSerializable
{

	/** @var string */
	private $version;

	/** @var string */
	private $appId;

	/** @var string */
	private $challenge;

	/** @var SignRequestCollection */
	private $requests;

	/**
	 * @param string $version
	 * @param string $appId
	 * @param string $challenge
	 */
	public function __construct($version, $appId, $challenge, SignRequestCollection $requests)
	{
		$this->version = $version;
		$this->appId = $appId;
		$this->challenge = $challenge;
		$this->requests = $requests;
	}

	/**
	 * @return string
	 */
	public function getAppId()
	{
		return $this->appId;
	}

	/**
	 * @return string
	 */
	public function getChallenge()
	{
		return $this->challenge;
	}

	/**
	 * @return SignRequestCollection
	 */
	public function getSignRequests()
	{
		return $this->requests;
	}

	/**
	 * @return string
	 */
	public function getVersion()
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
