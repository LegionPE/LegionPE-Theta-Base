<?php

/*
 * Theta
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

namespace legionpe\theta\query;

use legionpe\theta\BasePlugin;
use legionpe\theta\config\Settings;
use legionpe\theta\utils\MUtils;
use pocketmine\Player;
use pocketmine\Server;
use legionpe\theta\Session;
use pocketmine\utils\TextFormat;

class GetWarningsQuery extends AsyncQuery{
	private $name, $ip, $uuid, $clientId;
	private $warnings = [];
	public function __construct(BasePlugin $main, Session $sender){
		$this->name = $sender->getPlayer()->getName();
		$this->ip = $sender->getPlayer()->getAddress();
		$this->uuid = $sender->getPlayer()->getRawUniqueId();
		$this->clientId = $sender->getPlayer()->getClientId();
		parent::__construct($main);
	}
	public function getResultType(){
		return self::TYPE_ALL;
	}
	public function getQuery(){
		return "SELECT type,msg,created,duration FROM warnings WHERE name = '" . $this->name . "' OR ip = '" . $this->ip . "' OR uuid = '" . $this->uuid . "' OR clientid = '" . $this->clientId . "'";
	}
	public function getExpectedColumns(){
		return [
			"type" => self::COL_INT,
			"msg" => self::COL_STRING,
			"created" => self::COL_INT,
			"duration" => self::COL_INT
		];
	}
	public function onCompletion(Server $server){
		$main = BasePlugin::getInstance($server);
		$player = $main->getServer()->getPlayerExact($this->name);
		if(!$player->isOnline()){
			return;
		}
		$result = $this->getResult();
		if(count($result["result"]) === 0){
			return;
		}
		foreach($result["result"] as $row){
			if(((int) $row["type"]) == 0){
				$creation = (int) $row["created"];
				$duration = (int) $row["duration"];
				if(time() - $creation < $duration){
					$timeLeft = $duration - (time() - $creation);
					$player->kick(TextFormat::RED . "You have been banned. Time left: " . MUtils::time_secsToString($timeLeft) . " \n" . TextFormat::RED . "Message: " . TextFormat::AQUA . $row["msg"]);
				}
			}
		}
	}
}
