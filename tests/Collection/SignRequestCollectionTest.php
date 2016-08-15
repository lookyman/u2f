<?php
declare(strict_types=1);

namespace Lookyman\U2F\Tests\Collection;

use Lookyman\U2F\Collection\SignRequestCollection;
use Lookyman\U2F\Request\SignRequest;
use Lookyman\U2F\Response\SignResponse;

class SignRequestCollectionTest extends \PHPUnit_Framework_TestCase
{
	public function testGetSet()
	{
		$requests = new SignRequestCollection();
		$requests->add(new SignRequest('a', 'b', 'c', 'd'));
		$requests->add(new SignRequest('e', 'f', 'g', 'h'));
		self::assertCount(2, $requests);
		self::assertSame(2, count($requests));
	}

	public function testGetMatchingRequest()
	{
		$requests = new SignRequestCollection();
		$requests->add($request = new SignRequest('a', 'b', 'c', 'd'));

		self::assertSame($request, $requests->getMatchingRequest(new SignResponse('d', 'abcdef', '{"challenge":"Yw"}')));
	}

	public function testJsonSerialize()
	{
		$requests = new SignRequestCollection();
		$requests->add(new SignRequest('a', 'b', 'c', 'd'));

		self::assertSame(
			'[{"version":"a","challenge":"Yw","keyHandle":"ZA","appId":"b"}]',
			json_encode($requests)
		);
	}
}
