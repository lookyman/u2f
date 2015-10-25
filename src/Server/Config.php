<?php

namespace lookyman\U2f\Server;

class Config
{

	/** string */
	const U2F_VERSION = 'U2F_V2';

	/** @var string */
	private $appId;

	/** @var string|NULL */
	private $attestDir;

	/**
	 * @param string $appId
	 * @param string|NULL $attestDir
	 */
	public function __construct($appId, $attestDir = NULL)
	{
		$this->appId = $appId;
		$this->attestDir = $attestDir;
	}

	/**
	 * @return string
	 */
	public function getAppId()
	{
		return $this->appId;
	}

	/**
	 * @return string
	 */
	public function getVersion()
	{
		return self::U2F_VERSION;
	}

	/**
	 * @return string|NULL
	 */
	public function getAttestDir()
	{
		return $this->attestDir;
	}

}
