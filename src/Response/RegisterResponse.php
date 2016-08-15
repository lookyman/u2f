<?php
declare(strict_types=1);

namespace Lookyman\U2F\Response;

use Lookyman\U2F\Helpers;
use Nette\Utils\Json;

class RegisterResponse
{
	/**
	 * @var string
	 */
	private $certificate;

	/**
	 * @var string
	 */
	private $challenge;

	/**
	 * @var string
	 */
	private $clientData;

	/**
	 * @var string
	 */
	private $keyHandle;

	/**
	 * @var string
	 */
	private $publicKey;

	/**
	 * @var string
	 */
	private $signature;

	/**
	 * @param string $registrationData
	 * @param string $clientData
	 */
	public function __construct(string $registrationData, string $clientData)
	{
		$this->clientData = $clientData;
		$this->challenge = Helpers::urlSafeBase64Decode(Json::decode($clientData)->challenge);

		$this->publicKey = substr($registrationData, 1, Helpers::PUBLIC_KEY_LENGTH);
		Helpers::publicKey2Pem($this->publicKey);

		$regDataDecoded = array_values(unpack('C*', $registrationData));
		$this->keyHandle = substr($registrationData, Helpers::PUBLIC_KEY_LENGTH + 2, $regDataDecoded[Helpers::PUBLIC_KEY_LENGTH + 1]);

		$this->certificate = Helpers::fixSignatureUnusedBits(substr($registrationData, $regDataDecoded[Helpers::PUBLIC_KEY_LENGTH + 1] + Helpers::PUBLIC_KEY_LENGTH + 2, ($regDataDecoded[$regDataDecoded[Helpers::PUBLIC_KEY_LENGTH + 1] + Helpers::PUBLIC_KEY_LENGTH + 4] << 8) + $regDataDecoded[$regDataDecoded[Helpers::PUBLIC_KEY_LENGTH + 1] + Helpers::PUBLIC_KEY_LENGTH + 5] + 4));
		$this->signature = substr($registrationData, $regDataDecoded[Helpers::PUBLIC_KEY_LENGTH + 1] + Helpers::PUBLIC_KEY_LENGTH + strlen($this->certificate) + 2);
	}

	/**
	 * @return string
	 */
	public function getCertificate(): string
	{
		return $this->certificate;
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
	public function getClientData(): string
	{
		return $this->clientData;
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
	public function getPublicKey(): string
	{
		return $this->publicKey;
	}

	/**
	 * @return string
	 */
	public function getSignature(): string
	{
		return $this->signature;
	}
}
