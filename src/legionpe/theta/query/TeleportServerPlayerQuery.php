<?php

/*
 * LegionPE
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
use legionpe\theta\Session;
use pocketmine\Server;

class TeleportServerPlayerQuery extends AsyncQuery{
	/** @var string */
	private $name;
	/** @var int */
	private $sender;
	public function __construct(BasePlugin $main, $name, Session $session){
		$this->name = $name;
		$this->sender = $main->storeObject($session);
		parent::__construct($main);
	}
	public function getResultType(){
		return self::TYPE_ASSOC;
	}
	public function getExpectedColumns(){
		return [
			"offtime" => self::COL_INT,
			"status" => self::COL_INT,
			"status_ip" => self::COL_STRING,
			"status_port" => self::COL_INT,
		];
	}
	public function getQuery(){
		return "SELECT unix_timestamp()-laston AS offtime,status,status_ip,status_port FROM users WHERE name={$this->esc($this->name)}";
	}
	public function onCompletion(Server $server){
		$main = BasePlugin::getInstance($server);
		/** @var Session $sender */
		$sender = $main->fetchObject($this->sender);
		if($sender->getPlayer()->isOnline()){
			$result = $this->getResult();
			if($result["resulttype"] === self::TYPE_ASSOC){
				$result = $result["result"];
				$offtime = $result["offtime"];
				if($result["status"] === Settings::STATUS_ONLINE and $offtime < 15){
					$main->transfer($sender->getPlayer(), $result["status_ip"], $result["status_port"], "");
				}
			}
		}
	}
}
