<?php

namespace lookyman\U2f\Server;

use lookyman\U2f\Exception\IException;
use lookyman\U2f\Exception\PublicKeyException;

class Helpers
{

	/** int */
	const PUBLIC_KEY_LENGTH = 65;

	/**
	 * @param string $data
	 * @return string
	 */
	public static function urlSafeBase64Encode($data)
	{
		return trim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	/**
	 * @param string $data
	 * @return string
	 */
	public static function urlSafeBase64Decode($data)
	{
		return base64_decode(strtr($data, '-_', '+/'));
	}

	/**
	 * @param string $data
	 * @return string
	 */
	public static function publicKey2Pem($data)
	{
		if (strlen($data) !== self::PUBLIC_KEY_LENGTH || $data[0] !== "\x04") {
			throw new PublicKeyException('Decoding of public key failed.', IException::ERR_PUBKEY_DECODE);
		}

		return sprintf(
			"-----BEGIN PUBLIC KEY-----\r\n%s-----END PUBLIC KEY-----",
			chunk_split(base64_encode(sprintf(
				"\x30\x59\x30\x13\x06\x07\x2a\x86\x48\xce\x3d\x02\x01\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07\x03\x42\0%s",
				$data
			)), 64)
		);
	}

	/**
	 * @param string $data
	 * @return string
	 */
	public static function formatCert($data)
	{
		return sprintf(
			"-----BEGIN CERTIFICATE-----\r\n%s-----END CERTIFICATE-----",
			chunk_split(base64_encode($data), 64)
		);
	}

	/**
	 * @param string $certificate
	 * @return string
	 */
	public static function fixSignatureUnusedBits($certificate)
	{
		if (in_array(hash('sha256', $certificate), [
			'349bca1031f8c82c4ceca38b9cebf1a69df9fb3b94eed99eb3fb9aa3822d26e8',
			'dd574527df608e47ae45fbba75a2afdd5c20fd94a02419381813cd55a2a3398f',
			'1d8764f0f7cd1352df6150045c8f638e517270e8b5dda1c63ade9c2280240cae',
			'd0edc9a91a1677435a953390865d208c55b3183c6759c9b5a7ff494c322558eb',
			'6073c436dcd064a48127ddbf6032ac1a66fd59a0c24434f070d4e564c124c897',
			'ca993121846c464d666096d35f13bf44c1b05af205f9b4a1e00cf6cc10c5e511',
		])) {
			$certificate[strlen($certificate) - 257] = "\0";
		}
		return $certificate;
	}

}
