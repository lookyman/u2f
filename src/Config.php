<?php
declare(strict_types=1);

namespace Lookyman\U2F;

class Config
{
	const U2F_VERSION = 'U2F_V2';

	/** @var string */
	private $appId;

	/** @var string|null */
	private $attestDir;

	/**
	 * @param string $appId
	 * @param string|null $attestDir
	 */
	public function __construct(string $appId, $attestDir = null)
	{
		$this->appId = $appId;
		$this->attestDir = $attestDir;
	}

	/**
	 * @return string
	 */
	public function getAppId(): string
	{
		return $this->appId;
	}

	/**
	 * @return string
	 */
	public function getVersion(): string
	{
		return self::U2F_VERSION;
	}

	/**
	 * @return string|null
	 */
	public function getAttestDir()
	{
		return $this->attestDir;
	}
}
