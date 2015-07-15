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

namespace legionpe\theta\query;

use legionpe\theta\BasePlugin;
use pocketmine\Player;
use pocketmine\Server;

class NewUserQuery extends NextIdQuery{
	/** @var int */
	private $sesId;
	public function __construct(BasePlugin $plugin, Player $player){
		$this->sesId = $player->getId();
		parent::__construct($plugin, self::USER);
	}
	public function onCompletion(Server $server){
		$main = BasePlugin::getInstance($server);
		$result = $this->getResult();
		$uid = $result["result"]["id"];
		foreach($main->getServer()->getOnlinePlayers() as $player){
			if($player->getId() === $this->sesId){
				break;
			}
		}
		if(!isset($player)){
			return;
		}
		$main->newSession($player, BasePlugin::getDefaultLoginData($uid, $player));
	}
}
