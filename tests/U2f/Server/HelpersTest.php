<?php

namespace lookyman\U2f\Tests\Server;

use lookyman\U2f\Server\Helpers;

class HelpersTest extends \PHPUnit_Framework_TestCase
{

	public function testUrlSafeBase64Encode()
	{
		$this->assertSame('rcDG5o_x0kdfccJ_ekaXQSGD2_imxQJ-Ur0nXDO30E8', Helpers::urlSafeBase64Encode(base64_decode('rcDG5o/x0kdfccJ/ekaXQSGD2/imxQJ+Ur0nXDO30E8=')));
	}

	public function testUrlSafeBase64Decode()
	{
		$this->assertSame('rcDG5o/x0kdfccJ/ekaXQSGD2/imxQJ+Ur0nXDO30E8=', base64_encode(Helpers::urlSafeBase64Decode('rcDG5o_x0kdfccJ_ekaXQSGD2_imxQJ-Ur0nXDO30E8')));
	}

	/**
	 * @dataProvider publicKey2PemExceptionProvider
	 * @expectedException \lookyman\U2f\Exception\PublicKeyException
	 * @expectedExceptionCode \lookyman\U2f\Exception\IException::ERR_PUBKEY_DECODE
	 */
	public function testPublicKey2PemException($data)
	{
		Helpers::publicKey2Pem($data);
	}

	public function publicKey2PemExceptionProvider()
	{
		return [
			[''],
			[str_repeat('a', Helpers::PUBLIC_KEY_LENGTH)],
		];
	}

	public function testPublicKey2Pem()
	{
		$this->assertSame('LS0tLS1CRUdJTiBQVUJMSUMgS0VZLS0tLS0NCk1Ga3dFd1lIS29aSXpqMENBUVlJS29aSXpqMERBUWNEUWdBRUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUENCkFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUE9PQ0KLS0tLS1FTkQgUFVCTElDIEtFWS0tLS0t', Helpers::urlSafeBase64Encode(Helpers::publicKey2Pem($this->getPublicKey())));
	}

	public function testFormatCert()
	{
		$this->assertSame('LS0tLS1CRUdJTiBDRVJUSUZJQ0FURS0tLS0tDQpZV0pqWkdWbVoyaHBhbXRzYlc1dmNIRnljM1IxZG5kNGVYcEJRa05FUlVaSFNFbEtTMHhOVGs5UVVWSlRWRlZXDQpWMWhaV2c9PQ0KLS0tLS1FTkQgQ0VSVElGSUNBVEUtLS0tLQ', Helpers::urlSafeBase64Encode(Helpers::formatCert('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')));
	}

	private function getPublicKey()
	{
		return "\x04" . str_repeat("\0", Helpers::PUBLIC_KEY_LENGTH - 1);
	}

}
