<?php

namespace lookyman\U2f\Tests\Server;

use lookyman\U2f\Server\RegisterRequest;
use lookyman\U2f\Server\SignRequest;
use lookyman\U2f\Server\SignRequestCollection;

class RegisterRequestTest extends \PHPUnit_Framework_TestCase
{

	public function testGetSet()
	{
		$signRequests = (new SignRequestCollection)->add(new SignRequest('d', 'e', 'f', 'g'));
		$request = new RegisterRequest('a', 'b', 'c', $signRequests);
		$this->assertSame('a', $request->getVersion());
		$this->assertSame('b', $request->getAppId());
		$this->assertSame('c', $request->getChallenge());
		$this->assertCount(1, $request->getSignRequests());
		$this->assertSame($signRequests, $request->getSignRequests());
	}

	public function testJsonSerializable()
	{
		$this->assertSame(
			'{"version":"a","challenge":"Yw","appId":"b"}',
			json_encode(new RegisterRequest('a', 'b', 'c', (new SignRequestCollection)->add(new SignRequest('d', 'e', 'f', 'f'))))
		);
	}

}
