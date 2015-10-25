<?php

namespace lookyman\U2f\Tests\Server;

use lookyman\U2f\Server\SignRequest;
use lookyman\U2f\Server\SignRequestCollection;
use lookyman\U2f\Server\SignResponse;

class SignRequestCollectionTest extends \PHPUnit_Framework_TestCase
{

	public function testGetSet()
	{
		$requests = new SignRequestCollection;
		$requests->add(new SignRequest('a', 'b', 'c', 'd'));
		$requests->add(new SignRequest('e', 'f', 'g', 'h'));
		$this->assertCount(2, $requests);
		$this->assertSame(2, count($requests));
	}

	public function testGetMatchingRequest()
	{
		$requests = new SignRequestCollection;
		$requests->add($request = new SignRequest('a', 'b', 'c', 'd'));

		$this->assertSame($request, $requests->getMatchingRequest(new SignResponse('d', 'abcdef', '{"challenge":"Yw"}')));
	}

	public function testJsonSerialize()
	{
		$this->assertSame(
			'[{"version":"a","challenge":"Yw","keyHandle":"ZA","appId":"b"}]',
			json_encode((new SignRequestCollection)->add(new SignRequest('a', 'b', 'c', 'd')))
		);
	}

}
