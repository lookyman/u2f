<?php

namespace lookyman\U2f\Server;

use Nette\Utils\Json;

class SignResponse
{

	/** @var string */
	private $keyHandle;

	/** @var string */
	private $challenge;

	/** @var string */
	private $signaturePrefix;

	/** @var string */
	private $clientData;

	/** @var string */
	private $signature;

	/** @var int */
	private $counter;

	public function __construct($keyHandle, $signatureData, $clientData)
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
	public function getKeyHandle()
	{
		return $this->keyHandle;
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
	public function getSignaturePrefix()
	{
		return $this->signaturePrefix;
	}

	/**
	 * @return string
	 */
	public function getClientData()
	{
		return $this->clientData;
	}

	/**
	 * @return string
	 */
	public function getSignature()
	{
		return $this->signature;
	}

	/**
	 * @return int
	 */
	public function getCounter()
	{
		return $this->counter;
	}

}
