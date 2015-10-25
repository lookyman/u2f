<?php

namespace lookyman\U2f\Tests\Server;

use lookyman\U2f\Server\Helpers;
use lookyman\U2f\Server\SignResponse;

class SignResponseTest extends \PHPUnit_Framework_TestCase
{

	public function testGetSet()
	{
		$response = new SignResponse(
			Helpers::urlSafeBase64Decode('CTUayZo8hCBeC-sGQJChC0wW-bBg99bmOlGCgw8XGq4dLsxO3yWh9mRYArZxocP5hBB1pEGB3bbJYiM-5acc5w'),
			Helpers::urlSafeBase64Decode('AQAAAAQwRQIhAI6FSrMD3KUUtkpiP0jpIEakql-HNhwWFngyw553pS1CAiAKLjACPOhxzZXuZsVO8im-HStEcYGC50PKhsGp_SUAng=='),
			Helpers::urlSafeBase64Decode('eyAiY2hhbGxlbmdlIjogImZFbmM5b1Y3OUVhQmdLNUJvTkVSVTVnUEtNMlhHWVdyejRmVWpnYzBRN2ciLCAib3JpZ2luIjogImh0dHA6XC9cL2RlbW8uZXhhbXBsZS5jb20iLCAidHlwIjogIm5hdmlnYXRvci5pZC5nZXRBc3NlcnRpb24iIH0=')
		);

		$this->assertSame('CTUayZo8hCBeC-sGQJChC0wW-bBg99bmOlGCgw8XGq4dLsxO3yWh9mRYArZxocP5hBB1pEGB3bbJYiM-5acc5w', Helpers::urlSafeBase64Encode($response->getKeyHandle()));
		$this->assertSame('fEnc9oV79EaBgK5BoNERU5gPKM2XGYWrz4fUjgc0Q7g', Helpers::urlSafeBase64Encode($response->getChallenge()));
		$this->assertSame('AQAAAAQ', Helpers::urlSafeBase64Encode($response->getSignaturePrefix()));
		$this->assertSame('{ "challenge": "fEnc9oV79EaBgK5BoNERU5gPKM2XGYWrz4fUjgc0Q7g", "origin": "http:\/\/demo.example.com", "typ": "navigator.id.getAssertion" }', $response->getClientData());
		$this->assertSame('MEUCIQCOhUqzA9ylFLZKYj9I6SBGpKpfhzYcFhZ4MsOed6UtQgIgCi4wAjzocc2V7mbFTvIpvh0rRHGBgudDyobBqf0lAJ4', Helpers::urlSafeBase64Encode($response->getSignature()));
		$this->assertSame(4, $response->getCounter());
	}

}
