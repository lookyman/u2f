<?php

namespace lookyman\U2f\Server;

class SignRequest implements \JsonSerializable
{

	/** @var string */
	private $version;

	/** @var string */
	private $appId;

	/** @var string */
	private $challenge;

	/** @var string */
	private $keyHandle;

	/**
	 * @param string $version
	 * @param string $appId
	 * @param string $challenge
	 * @param string $keyHandle
	 */
	public function __construct($version, $appId, $challenge, $keyHandle)
	{
		$this->version = $version;
		$this->appId = $appId;
		$this->challenge = $challenge;
		$this->keyHandle = $keyHandle;
	}

	/**
	 * @return string
	 */
	public function getVersion()
	{
		return $this->version;
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
	 * @return string
	 */
	public function getKeyHandle()
	{
		return $this->keyHandle;
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
			'keyHandle' => Helpers::urlSafeBase64Encode($this->keyHandle),
			'appId' => $this->appId,
		];
	}

}
