<?php
declare(strict_types=1);

namespace Lookyman\U2F\Tests\Request;

use Lookyman\U2F\Collection\SignRequestCollection;
use Lookyman\U2F\Request\RegisterRequest;
use Lookyman\U2F\Request\SignRequest;

class RegisterRequestTest extends \PHPUnit_Framework_TestCase
{
	public function testGetSet()
	{
		$signRequests = new SignRequestCollection();
		$signRequests->add(new SignRequest('d', 'e', 'f', 'g'));

		$request = new RegisterRequest('a', 'b', 'c', $signRequests);

		self::assertSame('a', $request->getVersion());
		self::assertSame('b', $request->getAppId());
		self::assertSame('c', $request->getChallenge());
		self::assertCount(1, $request->getSignRequests());
		self::assertSame($signRequests, $request->getSignRequests());
	}

	public function testJsonSerializable()
	{
		$signRequests = new SignRequestCollection();
		$signRequests->add(new SignRequest('d', 'e', 'f', 'f'));

		self::assertSame(
			'{"version":"a","challenge":"Yw","appId":"b"}',
			json_encode(new RegisterRequest('a', 'b', 'c', $signRequests))
		);
	}
}
