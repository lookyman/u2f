<?php
declare(strict_types=1);

namespace Lookyman\U2F\Tests;

use Lookyman\U2F\Helpers;

class HelpersTest extends \PHPUnit_Framework_TestCase
{
	public function testUrlSafeBase64Encode()
	{
		self::assertSame('rcDG5o_x0kdfccJ_ekaXQSGD2_imxQJ-Ur0nXDO30E8', Helpers::urlSafeBase64Encode(base64_decode('rcDG5o/x0kdfccJ/ekaXQSGD2/imxQJ+Ur0nXDO30E8=')));
	}

	public function testUrlSafeBase64Decode()
	{
		self::assertSame('rcDG5o/x0kdfccJ/ekaXQSGD2/imxQJ+Ur0nXDO30E8=', base64_encode(Helpers::urlSafeBase64Decode('rcDG5o_x0kdfccJ_ekaXQSGD2_imxQJ-Ur0nXDO30E8')));
	}

	/**
	 * @dataProvider publicKey2PemExceptionProvider
	 * @expectedException \Lookyman\U2F\Exception\PublicKeyException
	 * @expectedExceptionCode \Lookyman\U2F\Exception\IException::ERR_PUBKEY_DECODE
	 */
	public function testPublicKey2PemException($data)
	{
		Helpers::publicKey2Pem($data);
	}

	/**
	 * @return array
	 */
	public function publicKey2PemExceptionProvider(): array
	{
		return [
			[''],
			[str_repeat('a', Helpers::PUBLIC_KEY_LENGTH)],
		];
	}

	public function testPublicKey2Pem()
	{
		self::assertSame('LS0tLS1CRUdJTiBQVUJMSUMgS0VZLS0tLS0NCk1Ga3dFd1lIS29aSXpqMENBUVlJS29aSXpqMERBUWNEUWdBRUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUENCkFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUE9PQ0KLS0tLS1FTkQgUFVCTElDIEtFWS0tLS0t', Helpers::urlSafeBase64Encode(Helpers::publicKey2Pem(getPublicKey())));
	}

	public function testFormatCert()
	{
		self::assertSame('LS0tLS1CRUdJTiBDRVJUSUZJQ0FURS0tLS0tDQpZV0pqWkdWbVoyaHBhbXRzYlc1dmNIRnljM1IxZG5kNGVYcEJRa05FUlVaSFNFbEtTMHhOVGs5UVVWSlRWRlZXDQpWMWhaV2c9PQ0KLS0tLS1FTkQgQ0VSVElGSUNBVEUtLS0tLQ', Helpers::urlSafeBase64Encode(Helpers::formatCert('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')));
	}
}
