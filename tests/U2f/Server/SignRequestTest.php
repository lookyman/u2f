<?php

namespace lookyman\U2f\Tests\Server;

use lookyman\U2f\Server\SignRequest;

class SignRequestTest extends \PHPUnit_Framework_TestCase
{

	public function testGetSet()
	{
		$request = new SignRequest('a', 'b', 'c', 'd');
		$this->assertSame('a', $request->getVersion());
		$this->assertSame('b', $request->getAppId());
		$this->assertSame('c', $request->getChallenge());
		$this->assertSame('d', $request->getKeyHandle());
	}

	public function testJsonSerialize()
	{
		$this->assertSame(
			'{"version":"a","challenge":"Yw","keyHandle":"ZA","appId":"b"}',
			json_encode(new SignRequest('a', 'b', 'c', 'd'))
		);
	}

}
