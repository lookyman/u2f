<?php
declare(strict_types=1);

namespace Lookyman\U2F;

class Registration
{
	/**
	 * @var string
	 */
	private $publicKey;

	/**
	 * @var string
	 */
	private $keyHandle;

	/**
	 * @var string|null
	 */
	private $certificate;

	/**
	 * @var int
	 */
	private $counter = -1;

	/**
	 * @param string $publicKey
	 * @param string $keyHandle
	 * @param string|null $certificate
	 */
	public function __construct(string $publicKey, string $keyHandle, $certificate = null)
	{
		$this->publicKey = $publicKey;
		Helpers::publicKey2Pem($this->publicKey);
		$this->keyHandle = $keyHandle;
		$this->certificate = $certificate;
	}

	/**
	 * @return string
	 */
	public function getPublicKey(): string
	{
		return $this->publicKey;
	}

	/**
	 * @return string
	 */
	public function getKeyHandle(): string
	{
		return $this->keyHandle;
	}

	/**
	 * @return string|null
	 */
	public function getCertificate()
	{
		return $this->certificate;
	}

	/**
	 * @return int
	 */
	public function getCounter(): int
	{
		return $this->counter;
	}

	/**
	 * @param int $value
	 */
	public function setCounter(int $value)
	{
		$this->counter = $value;
	}
}
