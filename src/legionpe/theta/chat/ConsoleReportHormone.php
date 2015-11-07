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

namespace legionpe\theta\chat;

use legionpe\theta\credentials\Credentials;
use legionpe\theta\utils\MUtils;
use legionpe\theta\utils\PostUrlTask;
use pocketmine\utils\TextFormat;

class ConsoleReportHormone extends Hormone{
	/** @var string */
	protected $ip;
	/** @var int */
	protected $port;
	public function execute(){
		$this->main->getLogger()->alert($this->src . "/$this->ip:$this->port /cs: " . TextFormat::YELLOW . $this->msg);
		foreach($this->main->getSessions() as $ses){
			if($ses->isModerator()){
				$ses->sendMessage($this->src . "/$this->ip:$this->port /cs:");
				$ses->sendMessage($this->msg);
			}
		}
	}
	public function getType(){
		return self::CONSOLE_MESSAGE;
	}
	public function onPostRelease($rowId){
		if($this->src === "{BOT}CapsDetector"){
			return;
		}
		$message = implode("\n", [
			"$this->src @ `$this->ip:$this->port` executed /console. Message `#$rowId``:",
			MUtils::toMd($this->msg),
		]);
		$isBot = substr($this->src, 0, 5) === "{BOT}";
		$this->main->getServer()->getScheduler()->scheduleAsyncTask(new PostUrlTask(Credentials::SLACK_WEBHOOK, json_encode([
//			"text" => $message,
//			"fallback" => TextFormat::clean($message),
			"text" => TextFormat::clean($message),
			"icon_url" => Credentials::LEGIONPE_ICON_URL,
			"username" => $isBot ? substr($this->src, 5) : $this->src,
			"channel" => $isBot ? "#spam" : "#support"
		])));
	}
}
