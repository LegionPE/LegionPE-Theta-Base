<?php

/*
 * LegionPE Theta
 *
 * Copyright (C) 2015 PEMapModder and contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PEMapModder
 */

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
	private $exMsg, $exFile, $exLine;
	/** @var string */
	private $when;
	public function __construct(\Exception $ex, $when){
		$this->exMsg = $ex->getMessage();
		$this->exFile = $ex->getFile();
		$this->exLine = $ex->getLine();
		$this->when = $when;
	}
	public function onRun(){
		if(microtime(true) - self::$last0 < 60){
			return;
		}
		$message = "=== ERROR BEGIN ===".PHP_EOL;
		$message .= "Exception: " . $this->exMsg.PHP_EOL;
		$message .= "File: " . $this->exFile . "#" . $this->exLine.PHP_EOL;
		$message .= "UNIX Timestamp: " . $this->when.PHP_EOL;
		$message .= "Time: ".date("H:i:s", $this->when).PHP_EOL;
		$message .= "Server: ".Settings::$LOCALIZE_IP . " " . Settings::$LOCALIZE_PORT." (".Settings::$LOCALIZE_ID.")".PHP_EOL;
		$message .= "=== END ERROR ===".PHP_EOL;
		self::log($message);
		self::$last0 = self::$last1;
		self::$last1 = self::$last2;
		self::$last2 = self::$last3;
		self::$last3 = microtime(true);
	}
	public static function log($line){
		echo $line, PHP_EOL;
		Utils::getURL(Credentials::IRC_WEBHOOK_NOPREFIX . urlencode($line));
		Utils::postURL(Credentials::SLACK_WEBHOOK, ["payload" => json_encode(["text" => $line, "username" => "Server-".Settings::$LOCALIZE_ID.""])]);
	}
}
