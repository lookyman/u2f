<?php

namespace lookyman\U2f\Entropy;

use lookyman\U2f\Exception\EntropyException;
use lookyman\U2f\Exception\IException;

class OpenSslEntropyProvider implements IEntropyProvider
{

	public function __construct()
	{
		if (OPENSSL_VERSION_NUMBER < 0x10000000) {
			throw new EntropyException(sprintf('OpenSSL has to be at least version 1.0.0, this is %s.', OPENSSL_VERSION_TEXT), IException::ERR_OLD_OPENSSL);
		}
	}

	public function getPseudoRandomBytes($length)
	{
		$data = openssl_random_pseudo_bytes((int) $length, $strong);
		if ($data === FALSE || $strong !== TRUE) {
			throw new EntropyException('Could not get enough cryptographically strong bytes of entropy.', IException::ERR_BAD_RANDOM);
		}
		return $data;
	}

}
