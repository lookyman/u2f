<?php

namespace lookyman\U2f\Exception;

interface IException
{

	/** Error for the authentication message not matching any outstanding authentication request */
	const ERR_NO_MATCHING_REQUEST = 1;

	/** Error for the authentication message not matching any registration */
	const ERR_NO_MATCHING_REGISTRATION = 2;

	/** Error for the signature on the authentication message not verifying with the correct key */
	const ERR_AUTHENTICATION_FAILURE = 3;

	/** Error for the challenge in the registration message not matching the registration challenge */
	const ERR_UNMATCHED_CHALLENGE = 4;

	/** Error for the attestation signature on the registration message not verifying */
	const ERR_ATTESTATION_SIGNATURE = 5;

	/** Error for the attestation verification not verifying */
	const ERR_ATTESTATION_VERIFICATION = 6;

	/** Error for not getting good random from the system */
	const ERR_BAD_RANDOM = 7;

	/** Error when the counter is lower than expected */
	const ERR_COUNTER_TOO_LOW = 8;

	/** Error decoding public key */
	const ERR_PUBKEY_DECODE = 9;

	/** Error user-agent returned error */
	// const ERR_BAD_UA_RETURNING = 10;

	/** Error old OpenSSL version */
	const ERR_OLD_OPENSSL = 11;

}
