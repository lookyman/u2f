<?php
declare(strict_types=1);

namespace Lookyman\U2F;

use Lookyman\U2F\Collection\RegistrationCollection;
use Lookyman\U2F\Collection\SignRequestCollection;
use Lookyman\U2F\Exception\AuthenticationException;
use Lookyman\U2F\Exception\IException;
use Lookyman\U2F\Exception\PublicKeyException;
use Lookyman\U2F\Exception\RegistrationException;
use Lookyman\U2F\Request\RegisterRequest;
use Lookyman\U2F\Request\SignRequest;
use Lookyman\U2F\Response\RegisterResponse;
use Lookyman\U2F\Response\SignResponse;
use Nette\Utils\Finder;

class Server
{
	const CHALLENGE_LENGTH = 32;

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @param Config $config
	 */
	public function __construct(Config $config)
	{
		$this->config = $config;
	}

	/**
	 * @return RegisterRequest
	 */
	public function createRegisterRequest(RegistrationCollection $registrations): RegisterRequest
	{
		return new RegisterRequest(
			$this->config->getVersion(),
			$this->config->getAppId(),
			$this->createChallenge(),
			$this->createSignRequests($registrations)
		);
	}

	/**
	 * @return Registration
	 */
	public function register(RegisterRequest $request, RegisterResponse $response): Registration
	{
		if ($response->getChallenge() !== $request->getChallenge()) {
			throw new RegistrationException('Registration challenge does not match.', IException::ERR_UNMATCHED_CHALLENGE);
		}

		$certificate = Helpers::formatCert($response->getCertificate());

		if ($this->checkAttest($certificate) !== true) {
			throw new RegistrationException('Attestation certificate can not be validated.', IException::ERR_ATTESTATION_VERIFICATION);

		} elseif (!openssl_pkey_get_public($certificate)) {
			throw new PublicKeyException('Decoding of public key failed.', IException::ERR_PUBKEY_DECODE);

		} elseif (openssl_verify(
			$this->getRegisterVerificationData($request, $response),
			$response->getSignature(),
			$certificate,
			OPENSSL_ALGO_SHA256
		) !== 1) {
			throw new RegistrationException('Attestation signature does not match.', IException::ERR_ATTESTATION_SIGNATURE);
		}

		return new Registration(
			$response->getPublicKey(),
			$response->getKeyHandle(),
			$response->getCertificate()
		);
	}

	/**
	 * @return SignRequestCollection
	 */
	public function createSignRequests(RegistrationCollection $registrations): SignRequestCollection
	{
		$requests = new SignRequestCollection();
		foreach ($registrations as $registration) {
			$requests->add(new SignRequest(
				$this->config->getVersion(),
				$this->config->getAppId(),
				$this->createChallenge(),
				$registration->getKeyHandle()
			));
		}
		return $requests;
	}

	/**
	 * @return Registration
	 */
	public function authenticate(SignRequestCollection $requests, RegistrationCollection $registrations, SignResponse $response): Registration
	{
		$registration = $registrations->getMatchingRegistration($response);

		if (openssl_verify(
			$this->getAuthenticateVerificationData($requests->getMatchingRequest($response), $response),
			$response->getSignature(),
			Helpers::publicKey2Pem($registration->getPublicKey()),
			OPENSSL_ALGO_SHA256
		) === 1) {
			$counter = $response->getCounter();
			// @todo wrap-around
			if ($counter > $registration->getCounter()) {
				$registration->setCounter($counter);
				return $registration;
			}
			throw new AuthenticationException('Counter too low.', IException::ERR_COUNTER_TOO_LOW);
		}

		throw new AuthenticationException('Authentication failed.', IException::ERR_AUTHENTICATION_FAILURE);
	}

	/**
	 * @return string
	 */
	private function createChallenge(): string
	{
		return random_bytes(self::CHALLENGE_LENGTH);
	}

	/**
	 * @return string
	 */
	private function getRegisterVerificationData(RegisterRequest $request, RegisterResponse $response): string
	{
		return sprintf(
			"\0%s%s%s%s",
			hash('sha256', $request->getAppId(), true),
			hash('sha256', $response->getClientData(), true),
			$response->getKeyHandle(),
			$response->getPublicKey()
		);
	}

	/**
	 * @return string
	 */
	private function getAuthenticateVerificationData(SignRequest $request, SignResponse $response): string
	{
		return sprintf(
			'%s%s%s',
			hash('sha256', $request->getAppId(), true),
			$response->getSignaturePrefix(),
			hash('sha256', $response->getClientData(), true)
		);
	}

	/**
	 * @param string $certificate
	 * @return bool
	 */
	private function checkAttest($certificate): bool
	{
		if (!$this->config->getAttestDir()) {
			return true;
		}

		// @todo Original purpose is -1 which is undocumented. Is ANY ok to use here?
		// https://github.com/Yubico/php-u2flib-server/blob/cd49f97017c8415be3e190397565719b5319d2d6/src/u2flib_server/U2F.php#L192
		return openssl_x509_checkpurpose($certificate, X509_PURPOSE_ANY, array_map(function ($file) {
			return $file->getPathName();
		}, iterator_to_array(Finder::findFiles('*.pem')->from($this->config->getAttestDir()), false))) === true;
	}
}
