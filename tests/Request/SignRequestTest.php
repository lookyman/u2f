<?php
declare(strict_types=1);

namespace Lookyman\U2F\Tests\Request;

use Lookyman\U2F\Request\SignRequest;

class SignRequestTest extends \PHPUnit_Framework_TestCase
{
	public function testGetSet()
	{
		$request = new SignRequest('a', 'b', 'c', 'd');
		self::assertSame('a', $request->getVersion());
		self::assertSame('b', $request->getAppId());
		self::assertSame('c', $request->getChallenge());
		self::assertSame('d', $request->getKeyHandle());
	}

	public function testJsonSerialize()
	{
		$this->assertSame(
			'{"version":"a","challenge":"Yw","keyHandle":"ZA","appId":"b"}',
			json_encode(new SignRequest('a', 'b', 'c', 'd'))
		);
	}
}
