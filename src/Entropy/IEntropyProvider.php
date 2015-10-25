<?php

namespace lookyman\U2f\Entropy;

interface IEntropyProvider
{

	/**
	 * @param int $length
	 * @return string
	 */
	function getPseudoRandomBytes($length);

}
