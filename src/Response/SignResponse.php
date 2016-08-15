<?php
declare(strict_types=1);

namespace Lookyman\U2F\Response;

use Lookyman\U2F\Helpers;
use Nette\Utils\Json;

class SignResponse
{
	/**
	 * @var string
	 */
	private $keyHandle;

	/**
	 * @var string
	 */
	private $challenge;

	/**
	 * @var string
	 */
	private $signaturePrefix;

	/**
	 * @var string
	 */
	private $clientData;

	/**
	 * @var string
	 */
	private $signature;

	/**
	 * @var int
	 */
	private $counter;

	/**
	 * @param string $keyHandle
	 * @param string $signatureData
	 * @param string $clientData
	 */
	public function __construct(string $keyHandle, string $signatureData, string $clientData)
	{
		$this->keyHandle = $keyHandle;
		$this->clientData = $clientData;
		$this->challenge = Helpers::urlSafeBase64Decode(Json::decode($clientData)->challenge);
		$this->signaturePrefix = substr($signatureData, 0, 5);
		$this->signature = substr($signatureData, 5);
		$this->counter = unpack('Nctr', substr($this->signaturePrefix, 1, 4))['ctr'];
	}

	/**
	 * @return string
	 */
	public function getKeyHandle(): string
	{
		return $this->keyHandle;
	}

	/**
	 * @return string
	 */
	public function getChallenge(): string
	{
		return $this->challenge;
	}

	/**
	 * @return string
	 */
	public function getSignaturePrefix(): string
	{
		return $this->signaturePrefix;
	}

	/**
	 * @return string
	 */
	public function getClientData(): string
	{
		return $this->clientData;
	}

	/**
	 * @return string
	 */
	public function getSignature(): string
	{
		return $this->signature;
	}

	/**
	 * @return int
	 */
	public function getCounter(): int
	{
		return $this->counter;
	}
}
