<?php

namespace legionpe\theta\utils;

use legionpe\theta\config\Settings;
use legionpe\theta\credentials\Credentials;
use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Utils;

class ReportErrorTask extends AsyncTask{
	private static $last0 = 0;
	private static $last1 = 0;
	private static $last2 = 0;
	private static $last3 = 0;
	/** @var \Exception */
	private $ex;
	/** @var string */
	private $when;
	public function __construct(\Exception $ex, $when){
		$this->ex = $ex;
		$this->when = $when;
	}
	public function onRun(){
		if(microtime(true) - self::$last0 < 60){
			return;
		}
		Utils::getURL(Credentials::IRC_WEBHOOK . urlencode("ping-all !!! PEMapModder ping!"));
		Utils::getURL(Credentials::IRC_WEBHOOK . urlencode("Exception caught: " . $this->ex->getMessage()));
		Utils::getURL(Credentials::IRC_WEBHOOK . urlencode("In file: " . $this->ex->getFile() . "#" . $this->ex->getLine()));
		Utils::getURL(Credentials::IRC_WEBHOOK . urlencode("Happened during: " . $this->when));
		Utils::getURL(Credentials::IRC_WEBHOOK . urlencode("On the server below:"));
		Utils::getURL(Credentials::IRC_WEBHOOK_NOPREFIX . urlencode("BotsHateNames: status " . Settings::$LOCALIZE_IP . " " . Settings::$LOCALIZE_PORT));
		self::$last0 = self::$last1;
		self::$last1 = self::$last2;
		self::$last2 = self::$last3;
		self::$last3 = microtime(true);
	}
}
