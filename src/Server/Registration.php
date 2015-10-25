<?php

namespace lookyman\U2f\Server;

class Registration
{

	/** @var string */
	private $publicKey;

	/** @var string */
	private $keyHandle;

	/** @var string|NULL */
	private $certificate;

	/** @var int */
	private $counter = -1;

	/**
	 * @param string $publicKey
	 * @param string $keyHandle
	 * @param string|NULL $certificate
	 */
	public function __construct($publicKey, $keyHandle, $certificate = NULL)
	{
		$this->publicKey = $publicKey;
		Helpers::publicKey2Pem($this->publicKey);

		$this->keyHandle = $keyHandle;
		$this->certificate = $certificate;
	}

	/**
	 * @return string
	 */
	public function getPublicKey()
	{
		return $this->publicKey;
	}

	/**
	 * @return string
	 */
	public function getKeyHandle()
	{
		return $this->keyHandle;
	}

	/**
	 * @return string|NULL
	 */
	public function getCertificate()
	{
		return $this->certificate;
	}

	/**
	 * @return int
	 */
	public function getCounter()
	{
		return $this->counter;
	}

	/**
	 * @param int $value
	 * @return self
	 */
	public function setCounter($value)
	{
		$this->counter = (int) $value;
		return $this;
	}

}
