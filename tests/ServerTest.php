<?php
declare(strict_types=1);

namespace Lookyman\U2F\Tests;

use Lookyman\U2F\Collection\RegistrationCollection;
use Lookyman\U2F\Collection\SignRequestCollection;
use Lookyman\U2F\Config;
use Lookyman\U2F\Helpers;
use Lookyman\U2F\Registration;
use Lookyman\U2F\Request\RegisterRequest;
use Lookyman\U2F\Request\SignRequest;
use Lookyman\U2F\Response\RegisterResponse;
use Lookyman\U2F\Response\SignResponse;
use Lookyman\U2F\Server;

class ServerTest extends \PHPUnit_Framework_TestCase
{
	public function testCreateRegisterRequest()
	{
		$server = new Server(new Config('a'));

		$registrationCollection = new RegistrationCollection();
		$registrationCollection->add(new Registration(getPublicKey(), 'b', 'c'));

		$request = $server->createRegisterRequest($registrationCollection);
		self::assertInstanceOf(RegisterRequest::class, $request);

		$signRequests = $request->getSignRequests();
		self::assertInstanceOf(SignRequestCollection::class, $signRequests);
		self::assertCount(1, $signRequests);
		list($first) = iterator_to_array($signRequests);

		self::assertSame(Config::U2F_VERSION, $first->getVersion());
		self::assertSame('a', $first->getAppId());
		self::assertInternalType('string', $first->getChallenge());
		self::assertSame(Server::CHALLENGE_LENGTH, strlen($first->getChallenge()));
		self::assertSame('b', $first->getKeyHandle());
	}

	public function testCreateSignRequests()
	{
		$server = new Server(new Config('a'));
		$registrations = new RegistrationCollection();
		$registrations->add(new Registration(getPublicKey(), 'b', 'c'));
		$registrations->add(new Registration(getPublicKey(), 'd', 'e'));

		$requests = $server->createSignRequests($registrations);
		self::assertInstanceOf(SignRequestCollection::class, $requests);
		self::assertCount(2, $requests);
		list($first, $second) = iterator_to_array($requests);

		self::assertSame(Config::U2F_VERSION, $first->getVersion());
		self::assertSame('a', $first->getAppId());
		self::assertInternalType('string', $first->getChallenge());
		self::assertSame(Server::CHALLENGE_LENGTH, strlen($first->getChallenge()));
		self::assertSame('b', $first->getKeyHandle());

		self::assertSame(Config::U2F_VERSION, $second->getVersion());
		self::assertSame('a', $second->getAppId());
		self::assertInternalType('string', $second->getChallenge());
		self::assertSame(Server::CHALLENGE_LENGTH, strlen($second->getChallenge()));
		self::assertSame('d', $second->getKeyHandle());
	}

	public function testRegister()
	{
		$config = new Config('http://demo.example.com');
		$server = new Server($config);

		$registration = $server->register(
			new RegisterRequest(
				$config->getVersion(),
				$config->getAppId(),
				Helpers::urlSafeBase64Decode('yKA0x075tjJ-GE7fKTfnzTOSaNUOWQxRd9TWz5aFOg8'),
				new SignRequestCollection()
			),
			new RegisterResponse(
				Helpers::urlSafeBase64Decode('BQQtEmhWVgvbh-8GpjsHbj_d5FB9iNoRL8mNEq34-ANufKWUpVdIj6BSB_m3eMoZ3GqnaDy3RA5eWP8mhTkT1Ht3QAk1GsmaPIQgXgvrBkCQoQtMFvmwYPfW5jpRgoMPFxquHS7MTt8lofZkWAK2caHD-YQQdaRBgd22yWIjPuWnHOcwggLiMIHLAgEBMA0GCSqGSIb3DQEBCwUAMB0xGzAZBgNVBAMTEll1YmljbyBVMkYgVGVzdCBDQTAeFw0xNDA1MTUxMjU4NTRaFw0xNDA2MTQxMjU4NTRaMB0xGzAZBgNVBAMTEll1YmljbyBVMkYgVGVzdCBFRTBZMBMGByqGSM49AgEGCCqGSM49AwEHA0IABNsK2_Uhx1zOY9ym4eglBg2U5idUGU-dJK8mGr6tmUQflaNxkQo6IOc-kV4T6L44BXrVeqN-dpCPr-KKlLYw650wDQYJKoZIhvcNAQELBQADggIBAJVAa1Bhfa2Eo7TriA_jMA8togoA2SUE7nL6Z99YUQ8LRwKcPkEpSpOsKYWJLaR6gTIoV3EB76hCiBaWN5HV3-CPyTyNsM2JcILsedPGeHMpMuWrbL1Wn9VFkc7B3Y1k3OmcH1480q9RpYIYr-A35zKedgV3AnvmJKAxVhv9GcVx0_CewHMFTryFuFOe78W8nFajutknarupekDXR4tVcmvj_ihJcST0j_Qggeo4_3wKT98CgjmBgjvKCd3Kqg8n9aSDVWyaOZsVOhZj3Fv5rFu895--D4qiPDETozJIyliH-HugoQpqYJaTX10mnmMdCa6aQeW9CEf-5QmbIP0S4uZAf7pKYTNmDQ5z27DVopqaFw00MIVqQkae_zSPX4dsNeeoTTXrwUGqitLaGap5ol81LKD9JdP3nSUYLfq0vLsHNDyNgb306TfbOenRRVsgQS8tJyLcknSKktWD_Qn7E5vjOXprXPrmdp7g5OPvrbz9QkWa1JTRfo2n2AXV02LPFc-UfR9bWCBEIJBxvmbpmqt0MnBTHWnth2b0CU_KJTDCY3kAPLGbOT8A4KiI73pRW-e9SWTaQXskw3Ei_dHRILM_l9OXsqoYHJ4Dd3tbfvmjoNYggSw4j50l3unI9d1qR5xlBFpW5sLr8gKX4bnY4SR2nyNiOQNLyPc0B0nW502aMEUCIQDTGOX-i_QrffJDY8XvKbPwMuBVrOSO-ayvTnWs_WSuDQIgZ7fMAvD_Ezyy5jg6fQeuOkoJi8V2naCtzV-HTly8Nww='),
				Helpers::urlSafeBase64Decode('eyAiY2hhbGxlbmdlIjogInlLQTB4MDc1dGpKLUdFN2ZLVGZuelRPU2FOVU9XUXhSZDlUV3o1YUZPZzgiLCAib3JpZ2luIjogImh0dHA6XC9cL2RlbW8uZXhhbXBsZS5jb20iLCAidHlwIjogIm5hdmlnYXRvci5pZC5maW5pc2hFbnJvbGxtZW50IiB9')
			)
		);

		self::assertSame('BC0SaFZWC9uH7wamOwduP93kUH2I2hEvyY0Srfj4A258pZSlV0iPoFIH-bd4yhncaqdoPLdEDl5Y_yaFORPUe3c', Helpers::urlSafeBase64Encode($registration->getPublicKey()));
		self::assertSame('CTUayZo8hCBeC-sGQJChC0wW-bBg99bmOlGCgw8XGq4dLsxO3yWh9mRYArZxocP5hBB1pEGB3bbJYiM-5acc5w', Helpers::urlSafeBase64Encode($registration->getKeyHandle()));
		self::assertSame('MIIC4jCBywIBATANBgkqhkiG9w0BAQsFADAdMRswGQYDVQQDExJZdWJpY28gVTJGIFRlc3QgQ0EwHhcNMTQwNTE1MTI1ODU0WhcNMTQwNjE0MTI1ODU0WjAdMRswGQYDVQQDExJZdWJpY28gVTJGIFRlc3QgRUUwWTATBgcqhkjOPQIBBggqhkjOPQMBBwNCAATbCtv1IcdczmPcpuHoJQYNlOYnVBlPnSSvJhq-rZlEH5WjcZEKOiDnPpFeE-i-OAV61XqjfnaQj6_iipS2MOudMA0GCSqGSIb3DQEBCwUAA4ICAQCVQGtQYX2thKO064gP4zAPLaIKANklBO5y-mffWFEPC0cCnD5BKUqTrCmFiS2keoEyKFdxAe-oQogWljeR1d_gj8k8jbDNiXCC7HnTxnhzKTLlq2y9Vp_VRZHOwd2NZNzpnB9ePNKvUaWCGK_gN-cynnYFdwJ75iSgMVYb_RnFcdPwnsBzBU68hbhTnu_FvJxWo7rZJ2q7qXpA10eLVXJr4_4oSXEk9I_0IIHqOP98Ck_fAoI5gYI7ygndyqoPJ_Wkg1VsmjmbFToWY9xb-axbvPefvg-KojwxE6MySMpYh_h7oKEKamCWk19dJp5jHQmumkHlvQhH_uUJmyD9EuLmQH-6SmEzZg0Oc9uw1aKamhcNNDCFakJGnv80j1-HbDXnqE0168FBqorS2hmqeaJfNSyg_SXT950lGC36tLy7BzQ8jYG99Ok32znp0UVbIEEvLSci3JJ0ipLVg_0J-xOb4zl6a1z65nae4OTj7628_UJFmtSU0X6Np9gF1dNizxXPlH0fW1ggRCCQcb5m6ZqrdDJwUx1p7Ydm9AlPyiUwwmN5ADyxmzk_AOCoiO96UVvnvUlk2kF7JMNxIv3R0SCzP5fTl7KqGByeA3d7W375o6DWIIEsOI-dJd7pyPXdakecZQRaVubC6_ICl-G52OEkdp8jYjkDS8j3NAdJ1udNmg', Helpers::urlSafeBase64Encode($registration->getCertificate()));
		self::assertSame(-1, $registration->getCounter());
	}

	/**
	 * @expectedException \Lookyman\U2F\Exception\RegistrationException
	 * @expectedExceptionCode \Lookyman\U2F\Exception\IException::ERR_UNMATCHED_CHALLENGE
	 */
	public function testRegisterUnmatchedChallenge()
	{
		$config = new Config('http://demo.example.com');
		$server = new Server($config);

		$server->register(
			new RegisterRequest(
				$config->getVersion(),
				$config->getAppId(), Helpers::urlSafeBase64Decode('yKA0x075tjJ-GE7fKTfnzTOSaNUOWQxRd9TWz5aFOg8'),
				new SignRequestCollection()
			),
			new RegisterResponse(
				Helpers::urlSafeBase64Decode('BQQtEmhWVgvbh-8GpjsHbj_d5FB9iNoRL8mNEq34-ANufKWUpVdIj6BSB_m3eMoZ3GqnaDy3RA5eWP8mhTkT1Ht3QAk1GsmaPIQgXgvrBkCQoQtMFvmwYPfW5jpRgoMPFxquHS7MTt8lofZkWAK2caHD-YQQdaRBgd22yWIjPuWnHOcwggLiMIHLAgEBMA0GCSqGSIb3DQEBCwUAMB0xGzAZBgNVBAMTEll1YmljbyBVMkYgVGVzdCBDQTAeFw0xNDA1MTUxMjU4NTRaFw0xNDA2MTQxMjU4NTRaMB0xGzAZBgNVBAMTEll1YmljbyBVMkYgVGVzdCBFRTBZMBMGByqGSM49AgEGCCqGSM49AwEHA0IABNsK2_Uhx1zOY9ym4eglBg2U5idUGU-dJK8mGr6tmUQflaNxkQo6IOc-kV4T6L44BXrVeqN-dpCPr-KKlLYw650wDQYJKoZIhvcNAQELBQADggIBAJVAa1Bhfa2Eo7TriA_jMA8togoA2SUE7nL6Z99YUQ8LRwKcPkEpSpOsKYWJLaR6gTIoV3EB76hCiBaWN5HV3-CPyTyNsM2JcILsedPGeHMpMuWrbL1Wn9VFkc7B3Y1k3OmcH1480q9RpYIYr-A35zKedgV3AnvmJKAxVhv9GcVx0_CewHMFTryFuFOe78W8nFajutknarupekDXR4tVcmvj_ihJcST0j_Qggeo4_3wKT98CgjmBgjvKCd3Kqg8n9aSDVWyaOZsVOhZj3Fv5rFu895--D4qiPDETozJIyliH-HugoQpqYJaTX10mnmMdCa6aQeW9CEf-5QmbIP0S4uZAf7pKYTNmDQ5z27DVopqaFw00MIVqQkae_zSPX4dsNeeoTTXrwUGqitLaGap5ol81LKD9JdP3nSUYLfq0vLsHNDyNgb306TfbOenRRVsgQS8tJyLcknSKktWD_Qn7E5vjOXprXPrmdp7g5OPvrbz9QkWa1JTRfo2n2AXV02LPFc-UfR9bWCBEIJBxvmbpmqt0MnBTHWnth2b0CU_KJTDCY3kAPLGbOT8A4KiI73pRW-e9SWTaQXskw3Ei_dHRILM_l9OXsqoYHJ4Dd3tbfvmjoNYggSw4j50l3unI9d1qR5xlBFpW5sLr8gKX4bnY4SR2nyNiOQNLyPc0B0nW502aMEUCIQDTGOX-i_QrffJDY8XvKbPwMuBVrOSO-ayvTnWs_WSuDQIgZ7fMAvD_Ezyy5jg6fQeuOkoJi8V2naCtzV-HTly8Nww='),
				Helpers::urlSafeBase64Decode('eyAiY2hhbGxlbmdlIjogIiIsICJvcmlnaW4iOiAiaHR0cDpcL1wvZGVtby5leGFtcGxlLmNvbSIsICJ0eXAiOiAibmF2aWdhdG9yLmlkLmZpbmlzaEVucm9sbG1lbnQiIH0')
			)
		);
	}

	/**
	 * @expectedException \Lookyman\U2F\Exception\RegistrationException
	 * @expectedExceptionCode \Lookyman\U2F\Exception\IException::ERR_ATTESTATION_SIGNATURE
	 */
	public function testRegisterFail()
	{
		$config = new Config('http://demo.example.com');
		$server = new Server($config);

		$server->register(
			new RegisterRequest(
				$config->getVersion(),
				$config->getAppId(),
				Helpers::urlSafeBase64Decode('yKA0x075tjJ-GE7fKTfnzTOSaNUOWQxRd9TWz5aFOg8'),
				new SignRequestCollection()
			),
			new RegisterResponse(
				Helpers::urlSafeBase64Decode('BQQtEmhWVgvbh-8GpjsHbj_d5FB9iNoRL8mNEq34-ANufKWUpVdIj6BSB_m3eMoZ3GqnaDy3RA5eWP8mhTkT1Ht3QAk1GsmaPIQgXgvrBkCQoQtMFvmwYPfW5jpRgoMPFxquHS7MTt8lofZkWAK2caHD-YQQdaRBgd22yWIjPuWnHOcwggLiMIHLAgEBMA0GCSqGSIb3DQEBCwUAMB0xGzAZBgNVBAMTEll1YmljbyBVMkYgVGVzdCBDQTAeFw0xNDA1MTUxMjU4NTRaFw0xNDA2MTQxMjU4NTRaMB0xGzAZBgNVBAMTEll1YmljbyBVMkYgVGVzdCBFRTBZMBMGByqGSM49AgEGCCqGSM49AwEHA0IABNsK2_Uhx1zOY9ym4eglBg2U5idUGU-dJK8mGr6tmUQflaNxkQo6IOc-kV4T6L44BXrVeqN-dpCPr-KKlLYw650wDQYJKoZIhvcNAQELBQADggIBAJVAa1Bhfa2Eo7TriA_jMA8togoA2SUE7nL6Z99YUQ8LRwKcPkEpSpOsKYWJLaR6gTIoV3EB76hCiBaWN5HV3-CPyTyNsM2JcILsedPGeHMpMuWrbL1Wn9VFkc7B3Y1k3OmcH1480q9RpYIYr-A35zKedgV3AnvmJKAxVhv9GcVx0_CewHMFTryFuFOe78W8nFajutknarupekDXR4tVcmvj_ihJcST0j_Qggeo4_3wKT98CgjmBgjvKCd3Kqg8n9aSDVWyaOZsVOhZj3Fv5rFu895--D4qiPDETozJIyliH-HugoQpqYJaTX10mnmMdCa6aQeW9CEf-5QmbIP0S4uZAf7pKYTNmDQ5z27DVopqaFw00MIVqQkae_zSPX4dsNeeoTTXrwUGqitLaGap5ol81LKD9JdP3nSUYLfq0vLsHNDyNgb306TfbOenRRVsgQS8tJyLcknSKktWD_Qn7E5vjOXprXPrmdp7g5OPvrbz9QkWa1JTRfo2n2AXV02LPFc-UfR9bWCBEIJBxvmbpmqt0MnBTHWnth2b0CU_KJTDCY3kAPLGbOT8A4KiI73pRW-e9SWTaQXskw3Ei_dHRILM_l9OXsqoYHJ4Dd3tbfvmjoNYggSw4j50l3unI9d1qR5xlBFpW5sLr8gKX4bnY4SR2nyNiOQNLyPc0B0nW502aMEUCIQDTGOX-i_QrffJDY8XvKbPwMuBVrOSO-ayvTnWs_WSuDQIgZ7fMAvD_Ezyy5jg6fQeuOkoJi8V2naCtzV-HTly8NwW='),
				Helpers::urlSafeBase64Decode('eyAiY2hhbGxlbmdlIjogInlLQTB4MDc1dGpKLUdFN2ZLVGZuelRPU2FOVU9XUXhSZDlUV3o1YUZPZzgiLCAib3JpZ2luIjogImh0dHA6XC9cL2RlbW8uZXhhbXBsZS5jb20iLCAidHlwIjogIm5hdmlnYXRvci5pZC5maW5pc2hFbnJvbGxtZW50IiB9')
			)
		);
	}

	public function testRegisterAttest()
	{
		$config = new Config('http://demo.example.com', __DIR__ . '/attest');
		$server = new Server($config);

		$server->register(
			new RegisterRequest(
				$config->getVersion(),
				$config->getAppId(),
				Helpers::urlSafeBase64Decode('5CBRhGBb2CXSum71GNREBGft7yz9g1jZO7JTkHGFsVY'),
				new SignRequestCollection()
			),
			new RegisterResponse(
				Helpers::urlSafeBase64Decode('BQRX1gfcG-ofTlk9rjB9spsIMrmT9ba0DLto5fzk8FDB05ModNU2sWAqoQRemYiUrILQdbNGpN_aHA0_oq8kcd_XQCK-Ut0PWaOtz43t0aAV04U788e-dvpeqLtHxtINjgmutKM8_GJQ7F-3W0dogUjSANuRYRdkkSEHPcVdLSkpyfowggIbMIIBBaADAgECAgRAxBIlMAsGCSqGSIb3DQEBCzAuMSwwKgYDVQQDEyNZdWJpY28gVTJGIFJvb3QgQ0EgU2VyaWFsIDQ1NzIwMDYzMTAgFw0xNDA4MDEwMDAwMDBaGA8yMDUwMDkwNDAwMDAwMFowKjEoMCYGA1UEAwwfWXViaWNvIFUyRiBFRSBTZXJpYWwgMTA4NjU5MTUyNTBZMBMGByqGSM49AgEGCCqGSM49AwEHA0IABK2iSVV7KGNEdPE-oHGvobNnHVw6ZZ6vB3jNIYB1C4t32OucHzMweHqM5CAMSMDHtfp1vuJYaiQSk7jb6M48WtejEjAQMA4GCisGAQQBgsQKAQEEADALBgkqhkiG9w0BAQsDggEBAVg0BoEHEEp4LJLYPYFACRGS8WZiXkCA8crYLgGnzvfKXwPwyKJlUzYxxv5xoRrl5zjkIUXhZ4mnHZVsnj9EY_VGDuRRzKX7YtxTZpFZn7ej3abjLhckTkkQ_AhUkmP7VuK2AWLgYsS8ejGUqughBsKvh_84uxTAEr5BS-OGg2yi7UIjd8W0nOCc6EN8d_8wCiPOjt2Y_-TKpLLTXKszk4UnWNzRdxBThmBBprJBZbF1VyVRvJm5yRLBpth3G8KMvrt4Nu3Ecoj_Q154IJpWe1Dp1upDFLOG9nWCRQk25Y264k9BDISfqs-wHvUjIo2iDnKl5UVoauTWaT7M6KuEwl4wRAIgYUVjS_yTwJAtF35glSbf9Et-5tJzlHOeAqmbACd6pwsCIE0MkTR5XNQoO4XqDaUZCXmadWu8yU1gfE7AJI9JUUcc'),
				Helpers::urlSafeBase64Decode('eyAiY2hhbGxlbmdlIjogIjVDQlJoR0JiMkNYU3VtNzFHTlJFQkdmdDd5ejlnMWpaTzdKVGtIR0ZzVlkiLCAib3JpZ2luIjogImh0dHA6XC9cL2RlbW8uZXhhbXBsZS5jb20iLCAidHlwIjogIm5hdmlnYXRvci5pZC5maW5pc2hFbnJvbGxtZW50IiB9')
			)
		);
	}

	/**
	 * @expectedException \Lookyman\U2F\Exception\RegistrationException
	 * @expectedExceptionCode \Lookyman\U2F\Exception\IException::ERR_ATTESTATION_VERIFICATION
	 */
	public function testRegisterAttestFail()
	{
		$config = new Config('http://demo.example.com', __DIR__ . '/attest');
		$server = new Server($config);

		$server->register(
			new RegisterRequest(
				$config->getVersion(),
				$config->getAppId(),
				Helpers::urlSafeBase64Decode('yKA0x075tjJ-GE7fKTfnzTOSaNUOWQxRd9TWz5aFOg8'),
				new SignRequestCollection()
			),
			new RegisterResponse(
				Helpers::urlSafeBase64Decode('BQQtEmhWVgvbh-8GpjsHbj_d5FB9iNoRL8mNEq34-ANufKWUpVdIj6BSB_m3eMoZ3GqnaDy3RA5eWP8mhTkT1Ht3QAk1GsmaPIQgXgvrBkCQoQtMFvmwYPfW5jpRgoMPFxquHS7MTt8lofZkWAK2caHD-YQQdaRBgd22yWIjPuWnHOcwggLiMIHLAgEBMA0GCSqGSIb3DQEBCwUAMB0xGzAZBgNVBAMTEll1YmljbyBVMkYgVGVzdCBDQTAeFw0xNDA1MTUxMjU4NTRaFw0xNDA2MTQxMjU4NTRaMB0xGzAZBgNVBAMTEll1YmljbyBVMkYgVGVzdCBFRTBZMBMGByqGSM49AgEGCCqGSM49AwEHA0IABNsK2_Uhx1zOY9ym4eglBg2U5idUGU-dJK8mGr6tmUQflaNxkQo6IOc-kV4T6L44BXrVeqN-dpCPr-KKlLYw650wDQYJKoZIhvcNAQELBQADggIBAJVAa1Bhfa2Eo7TriA_jMA8togoA2SUE7nL6Z99YUQ8LRwKcPkEpSpOsKYWJLaR6gTIoV3EB76hCiBaWN5HV3-CPyTyNsM2JcILsedPGeHMpMuWrbL1Wn9VFkc7B3Y1k3OmcH1480q9RpYIYr-A35zKedgV3AnvmJKAxVhv9GcVx0_CewHMFTryFuFOe78W8nFajutknarupekDXR4tVcmvj_ihJcST0j_Qggeo4_3wKT98CgjmBgjvKCd3Kqg8n9aSDVWyaOZsVOhZj3Fv5rFu895--D4qiPDETozJIyliH-HugoQpqYJaTX10mnmMdCa6aQeW9CEf-5QmbIP0S4uZAf7pKYTNmDQ5z27DVopqaFw00MIVqQkae_zSPX4dsNeeoTTXrwUGqitLaGap5ol81LKD9JdP3nSUYLfq0vLsHNDyNgb306TfbOenRRVsgQS8tJyLcknSKktWD_Qn7E5vjOXprXPrmdp7g5OPvrbz9QkWa1JTRfo2n2AXV02LPFc-UfR9bWCBEIJBxvmbpmqt0MnBTHWnth2b0CU_KJTDCY3kAPLGbOT8A4KiI73pRW-e9SWTaQXskw3Ei_dHRILM_l9OXsqoYHJ4Dd3tbfvmjoNYggSw4j50l3unI9d1qR5xlBFpW5sLr8gKX4bnY4SR2nyNiOQNLyPc0B0nW502aMEUCIQDTGOX-i_QrffJDY8XvKbPwMuBVrOSO-ayvTnWs_WSuDQIgZ7fMAvD_Ezyy5jg6fQeuOkoJi8V2naCtzV-HTly8Nww='),
				Helpers::urlSafeBase64Decode('eyAiY2hhbGxlbmdlIjogInlLQTB4MDc1dGpKLUdFN2ZLVGZuelRPU2FOVU9XUXhSZDlUV3o1YUZPZzgiLCAib3JpZ2luIjogImh0dHA6XC9cL2RlbW8uZXhhbXBsZS5jb20iLCAidHlwIjogIm5hdmlnYXRvci5pZC5maW5pc2hFbnJvbGxtZW50IiB9')
			)
		);
	}

	/**
	 * @expectedException \Lookyman\U2F\Exception\PublicKeyException
	 * @expectedExceptionCode \Lookyman\U2F\Exception\IException::ERR_PUBKEY_DECODE
	 */
	public function testRegisterInvalidCertificate()
	{
		$config = new Config('http://demo.example.com');
		$server = new Server($config);

		$server->register(
			new RegisterRequest(
				$config->getVersion(),
				$config->getAppId(),
				Helpers::urlSafeBase64Decode('yKA0x075tjJ-GE7fKTfnzTOSaNUOWQxRd9TWz5aFOg8'),
				new SignRequestCollection()
			),
			new RegisterResponse(
				Helpers::urlSafeBase64Decode('BQQtEmhWVgvbh-8GpjsHbj_d5FB9iNoRL8mNEq34-ANufKWUpVdIj6BSB_m3eMoZ3GqnaDy3RA5eWP8mhTkT1Ht3QAk1GsmaPIQgXgvrBkCQoQtMFvmwYPfW5jpRgoMPFxquHS7MTt8lofZkWAK2caHD-YQQdaRBgd22yWIjPuWnHOcwggLiMIHLAgEBMA0GCSqGSIb3DQEBCwUAMB0xGzAZBgNVBAMTEll1YmljbyBVMkYgVGVzdCBDQTAeFw0xNDA1MTUxMjU4NTRaFw0xNDA2MTQxMjU4NTRaMB0xGzAZBgNVBAMTEll1YmljbyBVMkYgVGVzdCBFRTBZMBMGByqGSM49AgEGCCqGSM49AwEHA0IABdsK2_Uhx1zOY9ym4eglBg2U5idUGU-dJK8mGr6tmUQflaNxkQo6IOc-kV4T6L44BXrVeqN-dpCPr-KKlLYw650wDQYJKoZIhvcNAQELBQADggIBAJVAa1Bhfa2Eo7TriA_jMA8togoA2SUE7nL6Z99YUQ8LRwKcPkEpSpOsKYWJLaR6gTIoV3EB76hCiBaWN5HV3-CPyTyNsM2JcILsedPGeHMpMuWrbL1Wn9VFkc7B3Y1k3OmcH1480q9RpYIYr-A35zKedgV3AnvmJKAxVhv9GcVx0_CewHMFTryFuFOe78W8nFajutknarupekDXR4tVcmvj_ihJcST0j_Qggeo4_3wKT98CgjmBgjvKCd3Kqg8n9aSDVWyaOZsVOhZj3Fv5rFu895--D4qiPDETozJIyliH-HugoQpqYJaTX10mnmMdCa6aQeW9CEf-5QmbIP0S4uZAf7pKYTNmDQ5z27DVopqaFw00MIVqQkae_zSPX4dsNeeoTTXrwUGqitLaGap5ol81LKD9JdP3nSUYLfq0vLsHNDyNgb306TfbOenRRVsgQS8tJyLcknSKktWD_Qn7E5vjOXprXPrmdp7g5OPvrbz9QkWa1JTRfo2n2AXV02LPFc-UfR9bWCBEIJBxvmbpmqt0MnBTHWnth2b0CU_KJTDCY3kAPLGbOT8A4KiI73pRW-e9SWTaQXskw3Ei_dHRILM_l9OXsqoYHJ4Dd3tbfvmjoNYggSw4j50l3unI9d1qR5xlBFpW5sLr8gKX4bnY4SR2nyNiOQNLyPc0B0nW502aMEUCIQDTGOX-i_QrffJDY8XvKbPwMuBVrOSO-ayvTnWs_WSuDQIgZ7fMAvD_Ezyy5jg6fQeuOkoJi8V2naCtzV-HTly8Nww='),
				Helpers::urlSafeBase64Decode('eyAiY2hhbGxlbmdlIjogInlLQTB4MDc1dGpKLUdFN2ZLVGZuelRPU2FOVU9XUXhSZDlUV3o1YUZPZzgiLCAib3JpZ2luIjogImh0dHA6XC9cL2RlbW8uZXhhbXBsZS5jb20iLCAidHlwIjogIm5hdmlnYXRvci5pZC5maW5pc2hFbnJvbGxtZW50IiB9')
			)
		);
	}

	/**
	 * @expectedException \Lookyman\U2F\Exception\PublicKeyException
	 * @expectedExceptionCode \Lookyman\U2F\Exception\IException::ERR_PUBKEY_DECODE
	 */
	public function testRegisterInvalidPublicKey()
	{
		$config = new Config('http://demo.example.com');
		$server = new Server($config);

		$server->register(
			new RegisterRequest(
				$config->getVersion(),
				$config->getAppId(),
				Helpers::urlSafeBase64Decode('yKA0x075tjJ-GE7fKTfnzTOSaNUOWQxRd9TWz5aFOg8'),
				new SignRequestCollection()
			),
			new RegisterResponse(
				Helpers::urlSafeBase64Decode('BQMtEmhWVgvbh-8GpjsHbj_d5FB9iNoRL8mNEq34-ANufKWUpVdIj6BSB_m3eMoZ3GqnaDy3RA5eWP8mhTkT1Ht3QAk1GsmaPIQgXgvrBkCQoQtMFvmwYPfW5jpRgoMPFxquHS7MTt8lofZkWAK2caHD-YQQdaRBgd22yWIjPuWnHOcwggLiMIHLAgEBMA0GCSqGSIb3DQEBCwUAMB0xGzAZBgNVBAMTEll1YmljbyBVMkYgVGVzdCBDQTAeFw0xNDA1MTUxMjU4NTRaFw0xNDA2MTQxMjU4NTRaMB0xGzAZBgNVBAMTEll1YmljbyBVMkYgVGVzdCBFRTBZMBMGByqGSM49AgEGCCqGSM49AwEHA0IABNsK2_Uhx1zOY9ym4eglBg2U5idUGU-dJK8mGr6tmUQflaNxkQo6IOc-kV4T6L44BXrVeqN-dpCPr-KKlLYw650wDQYJKoZIhvcNAQELBQADggIBAJVAa1Bhfa2Eo7TriA_jMA8togoA2SUE7nL6Z99YUQ8LRwKcPkEpSpOsKYWJLaR6gTIoV3EB76hCiBaWN5HV3-CPyTyNsM2JcILsedPGeHMpMuWrbL1Wn9VFkc7B3Y1k3OmcH1480q9RpYIYr-A35zKedgV3AnvmJKAxVhv9GcVx0_CewHMFTryFuFOe78W8nFajutknarupekDXR4tVcmvj_ihJcST0j_Qggeo4_3wKT98CgjmBgjvKCd3Kqg8n9aSDVWyaOZsVOhZj3Fv5rFu895--D4qiPDETozJIyliH-HugoQpqYJaTX10mnmMdCa6aQeW9CEf-5QmbIP0S4uZAf7pKYTNmDQ5z27DVopqaFw00MIVqQkae_zSPX4dsNeeoTTXrwUGqitLaGap5ol81LKD9JdP3nSUYLfq0vLsHNDyNgb306TfbOenRRVsgQS8tJyLcknSKktWD_Qn7E5vjOXprXPrmdp7g5OPvrbz9QkWa1JTRfo2n2AXV02LPFc-UfR9bWCBEIJBxvmbpmqt0MnBTHWnth2b0CU_KJTDCY3kAPLGbOT8A4KiI73pRW-e9SWTaQXskw3Ei_dHRILM_l9OXsqoYHJ4Dd3tbfvmjoNYggSw4j50l3unI9d1qR5xlBFpW5sLr8gKX4bnY4SR2nyNiOQNLyPc0B0nW502aMEUCIQDTGOX-i_QrffJDY8XvKbPwMuBVrOSO-ayvTnWs_WSuDQIgZ7fMAvD_Ezyy5jg6fQeuOkoJi8V2naCtzV-HTly8Nww='),
				Helpers::urlSafeBase64Decode('eyAiY2hhbGxlbmdlIjogInlLQTB4MDc1dGpKLUdFN2ZLVGZuelRPU2FOVU9XUXhSZDlUV3o1YUZPZzgiLCAib3JpZ2luIjogImh0dHA6XC9cL2RlbW8uZXhhbXBsZS5jb20iLCAidHlwIjogIm5hdmlnYXRvci5pZC5maW5pc2hFbnJvbGxtZW50IiB9')
			)
		);
	}

	public function testAuthenticate()
	{
		$config = new Config('http://demo.example.com');
		$server = new Server($config);

		$signRequestCollection = new SignRequestCollection();
		$signRequestCollection->add(new SignRequest(
			$config->getVersion(),
			$config->getAppId(),
			Helpers::urlSafeBase64Decode('fEnc9oV79EaBgK5BoNERU5gPKM2XGYWrz4fUjgc0Q7g'),
			Helpers::urlSafeBase64Decode('CTUayZo8hCBeC-sGQJChC0wW-bBg99bmOlGCgw8XGq4dLsxO3yWh9mRYArZxocP5hBB1pEGB3bbJYiM-5acc5w')
		));

		$registration = new Registration(
			Helpers::urlSafeBase64Decode('BC0SaFZWC9uH7wamOwduP93kUH2I2hEvyY0Srfj4A258pZSlV0iPoFIH-bd4yhncaqdoPLdEDl5Y_yaFORPUe3c'),
			Helpers::urlSafeBase64Decode('CTUayZo8hCBeC-sGQJChC0wW-bBg99bmOlGCgw8XGq4dLsxO3yWh9mRYArZxocP5hBB1pEGB3bbJYiM-5acc5w'),
			Helpers::urlSafeBase64Decode('MIIC4jCBywIBATANBgkqhkiG9w0BAQsFADAdMRswGQYDVQQDExJZdWJpY28gVTJGIFRlc3QgQ0EwHhcNMTQwNTE1MTI1ODU0WhcNMTQwNjE0MTI1ODU0WjAdMRswGQYDVQQDExJZdWJpY28gVTJGIFRlc3QgRUUwWTATBgcqhkjOPQIBBggqhkjOPQMBBwNCAATbCtv1IcdczmPcpuHoJQYNlOYnVBlPnSSvJhq-rZlEH5WjcZEKOiDnPpFeE-i-OAV61XqjfnaQj6_iipS2MOudMA0GCSqGSIb3DQEBCwUAA4ICAQCVQGtQYX2thKO064gP4zAPLaIKANklBO5y-mffWFEPC0cCnD5BKUqTrCmFiS2keoEyKFdxAe-oQogWljeR1d_gj8k8jbDNiXCC7HnTxnhzKTLlq2y9Vp_VRZHOwd2NZNzpnB9ePNKvUaWCGK_gN-cynnYFdwJ75iSgMVYb_RnFcdPwnsBzBU68hbhTnu_FvJxWo7rZJ2q7qXpA10eLVXJr4_4oSXEk9I_0IIHqOP98Ck_fAoI5gYI7ygndyqoPJ_Wkg1VsmjmbFToWY9xb-axbvPefvg-KojwxE6MySMpYh_h7oKEKamCWk19dJp5jHQmumkHlvQhH_uUJmyD9EuLmQH-6SmEzZg0Oc9uw1aKamhcNNDCFakJGnv80j1-HbDXnqE0168FBqorS2hmqeaJfNSyg_SXT950lGC36tLy7BzQ8jYG99Ok32znp0UVbIEEvLSci3JJ0ipLVg_0J-xOb4zl6a1z65nae4OTj7628_UJFmtSU0X6Np9gF1dNizxXPlH0fW1ggRCCQcb5m6ZqrdDJwUx1p7Ydm9AlPyiUwwmN5ADyxmzk_AOCoiO96UVvnvUlk2kF7JMNxIv3R0SCzP5fTl7KqGByeA3d7W375o6DWIIEsOI-dJd7pyPXdakecZQRaVubC6_ICl-G52OEkdp8jYjkDS8j3NAdJ1udNmg')
		);
		$registration->setCounter(3);

		$registrationCollection = new RegistrationCollection();
		$registrationCollection->add($registration);

		$registration = $server->authenticate(
			$signRequestCollection,
			$registrationCollection,
			new SignResponse(
				Helpers::urlSafeBase64Decode('CTUayZo8hCBeC-sGQJChC0wW-bBg99bmOlGCgw8XGq4dLsxO3yWh9mRYArZxocP5hBB1pEGB3bbJYiM-5acc5w'),
				Helpers::urlSafeBase64Decode('AQAAAAQwRQIhAI6FSrMD3KUUtkpiP0jpIEakql-HNhwWFngyw553pS1CAiAKLjACPOhxzZXuZsVO8im-HStEcYGC50PKhsGp_SUAng'),
				Helpers::urlSafeBase64Decode('eyAiY2hhbGxlbmdlIjogImZFbmM5b1Y3OUVhQmdLNUJvTkVSVTVnUEtNMlhHWVdyejRmVWpnYzBRN2ciLCAib3JpZ2luIjogImh0dHA6XC9cL2RlbW8uZXhhbXBsZS5jb20iLCAidHlwIjogIm5hdmlnYXRvci5pZC5nZXRBc3NlcnRpb24iIH0')
			)
		);

		self::assertSame('BC0SaFZWC9uH7wamOwduP93kUH2I2hEvyY0Srfj4A258pZSlV0iPoFIH-bd4yhncaqdoPLdEDl5Y_yaFORPUe3c', Helpers::urlSafeBase64Encode($registration->getPublicKey()));
		self::assertSame('CTUayZo8hCBeC-sGQJChC0wW-bBg99bmOlGCgw8XGq4dLsxO3yWh9mRYArZxocP5hBB1pEGB3bbJYiM-5acc5w', Helpers::urlSafeBase64Encode($registration->getKeyHandle()));
		self::assertSame('MIIC4jCBywIBATANBgkqhkiG9w0BAQsFADAdMRswGQYDVQQDExJZdWJpY28gVTJGIFRlc3QgQ0EwHhcNMTQwNTE1MTI1ODU0WhcNMTQwNjE0MTI1ODU0WjAdMRswGQYDVQQDExJZdWJpY28gVTJGIFRlc3QgRUUwWTATBgcqhkjOPQIBBggqhkjOPQMBBwNCAATbCtv1IcdczmPcpuHoJQYNlOYnVBlPnSSvJhq-rZlEH5WjcZEKOiDnPpFeE-i-OAV61XqjfnaQj6_iipS2MOudMA0GCSqGSIb3DQEBCwUAA4ICAQCVQGtQYX2thKO064gP4zAPLaIKANklBO5y-mffWFEPC0cCnD5BKUqTrCmFiS2keoEyKFdxAe-oQogWljeR1d_gj8k8jbDNiXCC7HnTxnhzKTLlq2y9Vp_VRZHOwd2NZNzpnB9ePNKvUaWCGK_gN-cynnYFdwJ75iSgMVYb_RnFcdPwnsBzBU68hbhTnu_FvJxWo7rZJ2q7qXpA10eLVXJr4_4oSXEk9I_0IIHqOP98Ck_fAoI5gYI7ygndyqoPJ_Wkg1VsmjmbFToWY9xb-axbvPefvg-KojwxE6MySMpYh_h7oKEKamCWk19dJp5jHQmumkHlvQhH_uUJmyD9EuLmQH-6SmEzZg0Oc9uw1aKamhcNNDCFakJGnv80j1-HbDXnqE0168FBqorS2hmqeaJfNSyg_SXT950lGC36tLy7BzQ8jYG99Ok32znp0UVbIEEvLSci3JJ0ipLVg_0J-xOb4zl6a1z65nae4OTj7628_UJFmtSU0X6Np9gF1dNizxXPlH0fW1ggRCCQcb5m6ZqrdDJwUx1p7Ydm9AlPyiUwwmN5ADyxmzk_AOCoiO96UVvnvUlk2kF7JMNxIv3R0SCzP5fTl7KqGByeA3d7W375o6DWIIEsOI-dJd7pyPXdakecZQRaVubC6_ICl-G52OEkdp8jYjkDS8j3NAdJ1udNmg', Helpers::urlSafeBase64Encode($registration->getCertificate()));
		self::assertSame(4, $registration->getCounter());
	}

	/**
	 * @expectedException \Lookyman\U2F\Exception\AuthenticationException
	 * @expectedExceptionCode \Lookyman\U2F\Exception\IException::ERR_COUNTER_TOO_LOW
	 */
	public function testAuthenticateCounter()
	{
		$config = new Config('http://demo.example.com');
		$server = new Server($config);

		$signRequestCollection = new SignRequestCollection();
		$signRequestCollection->add(new SignRequest(
			$config->getVersion(),
			$config->getAppId(),
			Helpers::urlSafeBase64Decode('fEnc9oV79EaBgK5BoNERU5gPKM2XGYWrz4fUjgc0Q7g'),
			Helpers::urlSafeBase64Decode('CTUayZo8hCBeC-sGQJChC0wW-bBg99bmOlGCgw8XGq4dLsxO3yWh9mRYArZxocP5hBB1pEGB3bbJYiM-5acc5w')
		));

		$registration = new Registration(
			Helpers::urlSafeBase64Decode('BC0SaFZWC9uH7wamOwduP93kUH2I2hEvyY0Srfj4A258pZSlV0iPoFIH-bd4yhncaqdoPLdEDl5Y_yaFORPUe3c'),
			Helpers::urlSafeBase64Decode('CTUayZo8hCBeC-sGQJChC0wW-bBg99bmOlGCgw8XGq4dLsxO3yWh9mRYArZxocP5hBB1pEGB3bbJYiM-5acc5w'),
			Helpers::urlSafeBase64Decode('MIIC4jCBywIBATANBgkqhkiG9w0BAQsFADAdMRswGQYDVQQDExJZdWJpY28gVTJGIFRlc3QgQ0EwHhcNMTQwNTE1MTI1ODU0WhcNMTQwNjE0MTI1ODU0WjAdMRswGQYDVQQDExJZdWJpY28gVTJGIFRlc3QgRUUwWTATBgcqhkjOPQIBBggqhkjOPQMBBwNCAATbCtv1IcdczmPcpuHoJQYNlOYnVBlPnSSvJhq-rZlEH5WjcZEKOiDnPpFeE-i-OAV61XqjfnaQj6_iipS2MOudMA0GCSqGSIb3DQEBCwUAA4ICAQCVQGtQYX2thKO064gP4zAPLaIKANklBO5y-mffWFEPC0cCnD5BKUqTrCmFiS2keoEyKFdxAe-oQogWljeR1d_gj8k8jbDNiXCC7HnTxnhzKTLlq2y9Vp_VRZHOwd2NZNzpnB9ePNKvUaWCGK_gN-cynnYFdwJ75iSgMVYb_RnFcdPwnsBzBU68hbhTnu_FvJxWo7rZJ2q7qXpA10eLVXJr4_4oSXEk9I_0IIHqOP98Ck_fAoI5gYI7ygndyqoPJ_Wkg1VsmjmbFToWY9xb-axbvPefvg-KojwxE6MySMpYh_h7oKEKamCWk19dJp5jHQmumkHlvQhH_uUJmyD9EuLmQH-6SmEzZg0Oc9uw1aKamhcNNDCFakJGnv80j1-HbDXnqE0168FBqorS2hmqeaJfNSyg_SXT950lGC36tLy7BzQ8jYG99Ok32znp0UVbIEEvLSci3JJ0ipLVg_0J-xOb4zl6a1z65nae4OTj7628_UJFmtSU0X6Np9gF1dNizxXPlH0fW1ggRCCQcb5m6ZqrdDJwUx1p7Ydm9AlPyiUwwmN5ADyxmzk_AOCoiO96UVvnvUlk2kF7JMNxIv3R0SCzP5fTl7KqGByeA3d7W375o6DWIIEsOI-dJd7pyPXdakecZQRaVubC6_ICl-G52OEkdp8jYjkDS8j3NAdJ1udNmg')
		);
		$registration->setCounter(5);

		$registrationCollection = new RegistrationCollection();
		$registrationCollection->add($registration);

		$server->authenticate(
			$signRequestCollection,
			$registrationCollection,
			new SignResponse(
				Helpers::urlSafeBase64Decode('CTUayZo8hCBeC-sGQJChC0wW-bBg99bmOlGCgw8XGq4dLsxO3yWh9mRYArZxocP5hBB1pEGB3bbJYiM-5acc5w'),
				Helpers::urlSafeBase64Decode('AQAAAAQwRQIhAI6FSrMD3KUUtkpiP0jpIEakql-HNhwWFngyw553pS1CAiAKLjACPOhxzZXuZsVO8im-HStEcYGC50PKhsGp_SUAng'),
				Helpers::urlSafeBase64Decode('eyAiY2hhbGxlbmdlIjogImZFbmM5b1Y3OUVhQmdLNUJvTkVSVTVnUEtNMlhHWVdyejRmVWpnYzBRN2ciLCAib3JpZ2luIjogImh0dHA6XC9cL2RlbW8uZXhhbXBsZS5jb20iLCAidHlwIjogIm5hdmlnYXRvci5pZC5nZXRBc3NlcnRpb24iIH0')
			)
		);
	}

	/**
	 * @expectedException \Lookyman\U2F\Exception\AuthenticationException
	 * @expectedExceptionCode \Lookyman\U2F\Exception\IException::ERR_AUTHENTICATION_FAILURE
	 */
	public function testAuthenticateFail()
	{
		$config = new Config('http://demo.example.com');
		$server = new Server($config);

		$signRequest = new SignRequest(
			$config->getVersion(),
			$config->getAppId(),
			Helpers::urlSafeBase64Decode('fEnc9oV79EaBgK5BoNERU5gPKM2XGYWrz4fUjgc0Q7g'),
			Helpers::urlSafeBase64Decode('CTUayZo8hCBeC-sGQJChC0wW-bBg99bmOlGCgw8XGq4dLsxO3yWh9mRYArZxocP5hBB1pEGB3bbJYiM-5acc5w')
		);

		$signRequestCollection = new SignRequestCollection();
		$signRequestCollection->add($signRequest);

		$registration = new Registration(
			Helpers::urlSafeBase64Decode('BC0SaFZWC9uH7wamOwduP93kUH2I2hEvyY0Srfj4A258pZSlV0iPoFIH-bd4yhncaqdoPLdEDl5Y_yaFORPUe3c'),
			Helpers::urlSafeBase64Decode('CTUayZo8hCBeC-sGQJChC0wW-bBg99bmOlGCgw8XGq4dLsxO3yWh9mRYArZxocP5hBB1pEGB3bbJYiM-5acc5w'),
			Helpers::urlSafeBase64Decode('MIIC4jCBywIBATANBgkqhkiG9w0BAQsFADAdMRswGQYDVQQDExJZdWJpY28gVTJGIFRlc3QgQ0EwHhcNMTQwNTE1MTI1ODU0WhcNMTQwNjE0MTI1ODU0WjAdMRswGQYDVQQDExJZdWJpY28gVTJGIFRlc3QgRUUwWTATBgcqhkjOPQIBBggqhkjOPQMBBwNCAATbCtv1IcdczmPcpuHoJQYNlOYnVBlPnSSvJhq-rZlEH5WjcZEKOiDnPpFeE-i-OAV61XqjfnaQj6_iipS2MOudMA0GCSqGSIb3DQEBCwUAA4ICAQCVQGtQYX2thKO064gP4zAPLaIKANklBO5y-mffWFEPC0cCnD5BKUqTrCmFiS2keoEyKFdxAe-oQogWljeR1d_gj8k8jbDNiXCC7HnTxnhzKTLlq2y9Vp_VRZHOwd2NZNzpnB9ePNKvUaWCGK_gN-cynnYFdwJ75iSgMVYb_RnFcdPwnsBzBU68hbhTnu_FvJxWo7rZJ2q7qXpA10eLVXJr4_4oSXEk9I_0IIHqOP98Ck_fAoI5gYI7ygndyqoPJ_Wkg1VsmjmbFToWY9xb-axbvPefvg-KojwxE6MySMpYh_h7oKEKamCWk19dJp5jHQmumkHlvQhH_uUJmyD9EuLmQH-6SmEzZg0Oc9uw1aKamhcNNDCFakJGnv80j1-HbDXnqE0168FBqorS2hmqeaJfNSyg_SXT950lGC36tLy7BzQ8jYG99Ok32znp0UVbIEEvLSci3JJ0ipLVg_0J-xOb4zl6a1z65nae4OTj7628_UJFmtSU0X6Np9gF1dNizxXPlH0fW1ggRCCQcb5m6ZqrdDJwUx1p7Ydm9AlPyiUwwmN5ADyxmzk_AOCoiO96UVvnvUlk2kF7JMNxIv3R0SCzP5fTl7KqGByeA3d7W375o6DWIIEsOI-dJd7pyPXdakecZQRaVubC6_ICl-G52OEkdp8jYjkDS8j3NAdJ1udNmg')
		);

		$registrationCollection = new RegistrationCollection();
		$registrationCollection->add($registration);

		$server->authenticate(
			$signRequestCollection,
			$registrationCollection,
			new SignResponse(
				Helpers::urlSafeBase64Decode('CTUayZo8hCBeC-sGQJChC0wW-bBg99bmOlGCgw8XGq4dLsxO3yWh9mRYArZxocP5hBB1pEGB3bbJYiM-5acc5w'),
				Helpers::urlSafeBase64Decode('AQAAAAQwRQIhAI6FSrMD3KUUtkpiP0jpIEakql-HNhwWFngyw553pS1CAiAKLjACPOhxzZXuZsVO8im-HStEcYGC50PKhsGp_SUAnG'),
				Helpers::urlSafeBase64Decode('eyAiY2hhbGxlbmdlIjogImZFbmM5b1Y3OUVhQmdLNUJvTkVSVTVnUEtNMlhHWVdyejRmVWpnYzBRN2ciLCAib3JpZ2luIjogImh0dHA6XC9cL2RlbW8uZXhhbXBsZS5jb20iLCAidHlwIjogIm5hdmlnYXRvci5pZC5nZXRBc3NlcnRpb24iIH0')
			)
		);
	}

	/**
	 * @expectedException \Lookyman\U2F\Exception\SignRequestException
	 * @expectedExceptionCode \Lookyman\U2F\Exception\IException::ERR_NO_MATCHING_REQUEST
	 */
	public function testAuthenticateWrongRequest()
	{
		$config = new Config('http://demo.example.com');
		$server = new Server($config);

		$signRequestCollection = new SignRequestCollection();
		$signRequestCollection->add(new SignRequest(
			$config->getVersion(),
			$config->getAppId(),
			Helpers::urlSafeBase64Decode('fEnc9oV79EaBgK5BoNERU5gPKM2XGYWrz4fUjgc0Q7g'),
			Helpers::urlSafeBase64Decode('cTUayZo8hCBeC-sGQJChC0wW-bBg99bmOlGCgw8XGq4dLsxO3yWh9mRYArZxocP5hBB1pEGB3bbJYiM-5acc5w')
		));

		$registrationCollection = new RegistrationCollection();
		$registrationCollection->add(new Registration(
			Helpers::urlSafeBase64Decode('BC0SaFZWC9uH7wamOwduP93kUH2I2hEvyY0Srfj4A258pZSlV0iPoFIH-bd4yhncaqdoPLdEDl5Y_yaFORPUe3c'),
			Helpers::urlSafeBase64Decode('CTUayZo8hCBeC-sGQJChC0wW-bBg99bmOlGCgw8XGq4dLsxO3yWh9mRYArZxocP5hBB1pEGB3bbJYiM-5acc5w'),
			Helpers::urlSafeBase64Decode('MIIC4jCBywIBATANBgkqhkiG9w0BAQsFADAdMRswGQYDVQQDExJZdWJpY28gVTJGIFRlc3QgQ0EwHhcNMTQwNTE1MTI1ODU0WhcNMTQwNjE0MTI1ODU0WjAdMRswGQYDVQQDExJZdWJpY28gVTJGIFRlc3QgRUUwWTATBgcqhkjOPQIBBggqhkjOPQMBBwNCAATbCtv1IcdczmPcpuHoJQYNlOYnVBlPnSSvJhq-rZlEH5WjcZEKOiDnPpFeE-i-OAV61XqjfnaQj6_iipS2MOudMA0GCSqGSIb3DQEBCwUAA4ICAQCVQGtQYX2thKO064gP4zAPLaIKANklBO5y-mffWFEPC0cCnD5BKUqTrCmFiS2keoEyKFdxAe-oQogWljeR1d_gj8k8jbDNiXCC7HnTxnhzKTLlq2y9Vp_VRZHOwd2NZNzpnB9ePNKvUaWCGK_gN-cynnYFdwJ75iSgMVYb_RnFcdPwnsBzBU68hbhTnu_FvJxWo7rZJ2q7qXpA10eLVXJr4_4oSXEk9I_0IIHqOP98Ck_fAoI5gYI7ygndyqoPJ_Wkg1VsmjmbFToWY9xb-axbvPefvg-KojwxE6MySMpYh_h7oKEKamCWk19dJp5jHQmumkHlvQhH_uUJmyD9EuLmQH-6SmEzZg0Oc9uw1aKamhcNNDCFakJGnv80j1-HbDXnqE0168FBqorS2hmqeaJfNSyg_SXT950lGC36tLy7BzQ8jYG99Ok32znp0UVbIEEvLSci3JJ0ipLVg_0J-xOb4zl6a1z65nae4OTj7628_UJFmtSU0X6Np9gF1dNizxXPlH0fW1ggRCCQcb5m6ZqrdDJwUx1p7Ydm9AlPyiUwwmN5ADyxmzk_AOCoiO96UVvnvUlk2kF7JMNxIv3R0SCzP5fTl7KqGByeA3d7W375o6DWIIEsOI-dJd7pyPXdakecZQRaVubC6_ICl-G52OEkdp8jYjkDS8j3NAdJ1udNmg')
		));

		$server->authenticate(
			$signRequestCollection,
			$registrationCollection,
			new SignResponse(
				Helpers::urlSafeBase64Decode('CTUayZo8hCBeC-sGQJChC0wW-bBg99bmOlGCgw8XGq4dLsxO3yWh9mRYArZxocP5hBB1pEGB3bbJYiM-5acc5w'),
				Helpers::urlSafeBase64Decode('AQAAAAQwRQIhAI6FSrMD3KUUtkpiP0jpIEakql-HNhwWFngyw553pS1CAiAKLjACPOhxzZXuZsVO8im-HStEcYGC50PKhsGp_SUAng'),
				Helpers::urlSafeBase64Decode('eyAiY2hhbGxlbmdlIjogImZFbmM5b1Y3OUVhQmdLNUJvTkVSVTVnUEtNMlhHWVdyejRmVWpnYzBRN2ciLCAib3JpZ2luIjogImh0dHA6XC9cL2RlbW8uZXhhbXBsZS5jb20iLCAidHlwIjogIm5hdmlnYXRvci5pZC5nZXRBc3NlcnRpb24iIH0')
			)
		);
	}

	/**
	 * @expectedException \Lookyman\U2F\Exception\RegistrationException
	 * @expectedExceptionCode \Lookyman\U2F\Exception\IException::ERR_NO_MATCHING_REGISTRATION
	 */
	public function testAuthenticateWrongRegistration()
	{
		$config = new Config('http://demo.example.com');
		$server = new Server($config);

		$signRequestCollection = new SignRequestCollection();
		$signRequestCollection->add(new SignRequest(
			$config->getVersion(),
			$config->getAppId(),
			Helpers::urlSafeBase64Decode('fEnc9oV79EaBgK5BoNERU5gPKM2XGYWrz4fUjgc0Q7g'),
			Helpers::urlSafeBase64Decode('CTUayZo8hCBeC-sGQJChC0wW-bBg99bmOlGCgw8XGq4dLsxO3yWh9mRYArZxocP5hBB1pEGB3bbJYiM-5acc5w')
		));

		$registrationCollection = new RegistrationCollection();
		$registrationCollection->add(new Registration(
			Helpers::urlSafeBase64Decode('BC0SaFZWC9uH7wamOwduP93kUH2I2hEvyY0Srfj4A258pZSlV0iPoFIH-bd4yhncaqdoPLdEDl5Y_yaFORPUe3c'),
			Helpers::urlSafeBase64Decode('cTUayZo8hCBeC-sGQJChC0wW-bBg99bmOlGCgw8XGq4dLsxO3yWh9mRYArZxocP5hBB1pEGB3bbJYiM-5acc5w'),
			Helpers::urlSafeBase64Decode('MIIC4jCBywIBATANBgkqhkiG9w0BAQsFADAdMRswGQYDVQQDExJZdWJpY28gVTJGIFRlc3QgQ0EwHhcNMTQwNTE1MTI1ODU0WhcNMTQwNjE0MTI1ODU0WjAdMRswGQYDVQQDExJZdWJpY28gVTJGIFRlc3QgRUUwWTATBgcqhkjOPQIBBggqhkjOPQMBBwNCAATbCtv1IcdczmPcpuHoJQYNlOYnVBlPnSSvJhq-rZlEH5WjcZEKOiDnPpFeE-i-OAV61XqjfnaQj6_iipS2MOudMA0GCSqGSIb3DQEBCwUAA4ICAQCVQGtQYX2thKO064gP4zAPLaIKANklBO5y-mffWFEPC0cCnD5BKUqTrCmFiS2keoEyKFdxAe-oQogWljeR1d_gj8k8jbDNiXCC7HnTxnhzKTLlq2y9Vp_VRZHOwd2NZNzpnB9ePNKvUaWCGK_gN-cynnYFdwJ75iSgMVYb_RnFcdPwnsBzBU68hbhTnu_FvJxWo7rZJ2q7qXpA10eLVXJr4_4oSXEk9I_0IIHqOP98Ck_fAoI5gYI7ygndyqoPJ_Wkg1VsmjmbFToWY9xb-axbvPefvg-KojwxE6MySMpYh_h7oKEKamCWk19dJp5jHQmumkHlvQhH_uUJmyD9EuLmQH-6SmEzZg0Oc9uw1aKamhcNNDCFakJGnv80j1-HbDXnqE0168FBqorS2hmqeaJfNSyg_SXT950lGC36tLy7BzQ8jYG99Ok32znp0UVbIEEvLSci3JJ0ipLVg_0J-xOb4zl6a1z65nae4OTj7628_UJFmtSU0X6Np9gF1dNizxXPlH0fW1ggRCCQcb5m6ZqrdDJwUx1p7Ydm9AlPyiUwwmN5ADyxmzk_AOCoiO96UVvnvUlk2kF7JMNxIv3R0SCzP5fTl7KqGByeA3d7W375o6DWIIEsOI-dJd7pyPXdakecZQRaVubC6_ICl-G52OEkdp8jYjkDS8j3NAdJ1udNmg')
		));

		$server->authenticate(
			$signRequestCollection,
			$registrationCollection,
			new SignResponse(
				Helpers::urlSafeBase64Decode('CTUayZo8hCBeC-sGQJChC0wW-bBg99bmOlGCgw8XGq4dLsxO3yWh9mRYArZxocP5hBB1pEGB3bbJYiM-5acc5w'),
				Helpers::urlSafeBase64Decode('AQAAAAQwRQIhAI6FSrMD3KUUtkpiP0jpIEakql-HNhwWFngyw553pS1CAiAKLjACPOhxzZXuZsVO8im-HStEcYGC50PKhsGp_SUAng'),
				Helpers::urlSafeBase64Decode('eyAiY2hhbGxlbmdlIjogImZFbmM5b1Y3OUVhQmdLNUJvTkVSVTVnUEtNMlhHWVdyejRmVWpnYzBRN2ciLCAib3JpZ2luIjogImh0dHA6XC9cL2RlbW8uZXhhbXBsZS5jb20iLCAidHlwIjogIm5hdmlnYXRvci5pZC5nZXRBc3NlcnRpb24iIH0')
			)
		);
	}

	/**
	 * @expectedException \Lookyman\U2F\Exception\PublicKeyException
	 * @expectedExceptionCode \Lookyman\U2F\Exception\IException::ERR_PUBKEY_DECODE
	 */
	public function testAuthenticateInvalidPublicKey()
	{
		$config = new Config('http://demo.example.com');
		$server = new Server($config);

		$signRequestCollection = new SignRequestCollection();
		$signRequestCollection->add(new SignRequest(
			$config->getVersion(),
			$config->getAppId(),
			Helpers::urlSafeBase64Decode('fEnc9oV79EaBgK5BoNERU5gPKM2XGYWrz4fUjgc0Q7g'),
			Helpers::urlSafeBase64Decode('CTUayZo8hCBeC-sGQJChC0wW-bBg99bmOlGCgw8XGq4dLsxO3yWh9mRYArZxocP5hBB1pEGB3bbJYiM-5acc5w')
		));

		$registrationCollection = new RegistrationCollection();
		$registrationCollection->add(new Registration(
			Helpers::urlSafeBase64Decode('bC0SaFZWC9uH7wamOwduP93kUH2I2hEvyY0Srfj4A258pZSlV0iPoFIH-bd4yhncaqdoPLdEDl5Y_yaFORPUe3c'),
			Helpers::urlSafeBase64Decode('CTUayZo8hCBeC-sGQJChC0wW-bBg99bmOlGCgw8XGq4dLsxO3yWh9mRYArZxocP5hBB1pEGB3bbJYiM-5acc5w'),
			Helpers::urlSafeBase64Decode('MIIC4jCBywIBATANBgkqhkiG9w0BAQsFADAdMRswGQYDVQQDExJZdWJpY28gVTJGIFRlc3QgQ0EwHhcNMTQwNTE1MTI1ODU0WhcNMTQwNjE0MTI1ODU0WjAdMRswGQYDVQQDExJZdWJpY28gVTJGIFRlc3QgRUUwWTATBgcqhkjOPQIBBggqhkjOPQMBBwNCAATbCtv1IcdczmPcpuHoJQYNlOYnVBlPnSSvJhq-rZlEH5WjcZEKOiDnPpFeE-i-OAV61XqjfnaQj6_iipS2MOudMA0GCSqGSIb3DQEBCwUAA4ICAQCVQGtQYX2thKO064gP4zAPLaIKANklBO5y-mffWFEPC0cCnD5BKUqTrCmFiS2keoEyKFdxAe-oQogWljeR1d_gj8k8jbDNiXCC7HnTxnhzKTLlq2y9Vp_VRZHOwd2NZNzpnB9ePNKvUaWCGK_gN-cynnYFdwJ75iSgMVYb_RnFcdPwnsBzBU68hbhTnu_FvJxWo7rZJ2q7qXpA10eLVXJr4_4oSXEk9I_0IIHqOP98Ck_fAoI5gYI7ygndyqoPJ_Wkg1VsmjmbFToWY9xb-axbvPefvg-KojwxE6MySMpYh_h7oKEKamCWk19dJp5jHQmumkHlvQhH_uUJmyD9EuLmQH-6SmEzZg0Oc9uw1aKamhcNNDCFakJGnv80j1-HbDXnqE0168FBqorS2hmqeaJfNSyg_SXT950lGC36tLy7BzQ8jYG99Ok32znp0UVbIEEvLSci3JJ0ipLVg_0J-xOb4zl6a1z65nae4OTj7628_UJFmtSU0X6Np9gF1dNizxXPlH0fW1ggRCCQcb5m6ZqrdDJwUx1p7Ydm9AlPyiUwwmN5ADyxmzk_AOCoiO96UVvnvUlk2kF7JMNxIv3R0SCzP5fTl7KqGByeA3d7W375o6DWIIEsOI-dJd7pyPXdakecZQRaVubC6_ICl-G52OEkdp8jYjkDS8j3NAdJ1udNmg')
		));

		$server->authenticate(
			$signRequestCollection,
			$registrationCollection,
			new SignResponse(
				Helpers::urlSafeBase64Decode('CTUayZo8hCBeC-sGQJChC0wW-bBg99bmOlGCgw8XGq4dLsxO3yWh9mRYArZxocP5hBB1pEGB3bbJYiM-5acc5w'),
				Helpers::urlSafeBase64Decode('AQAAAAQwRQIhAI6FSrMD3KUUtkpiP0jpIEakql-HNhwWFngyw553pS1CAiAKLjACPOhxzZXuZsVO8im-HStEcYGC50PKhsGp_SUAng'),
				Helpers::urlSafeBase64Decode('eyAiY2hhbGxlbmdlIjogImZFbmM5b1Y3OUVhQmdLNUJvTkVSVTVnUEtNMlhHWVdyejRmVWpnYzBRN2ciLCAib3JpZ2luIjogImh0dHA6XC9cL2RlbW8uZXhhbXBsZS5jb20iLCAidHlwIjogIm5hdmlnYXRvci5pZC5nZXRBc3NlcnRpb24iIH0')
			)
		);
	}
}
