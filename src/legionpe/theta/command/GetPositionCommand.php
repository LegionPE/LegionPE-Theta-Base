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

namespace legionpe\theta\command;

use legionpe\theta\BasePlugin;
use legionpe\theta\lang\Phrases;
use legionpe\theta\Session;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class GetPositionCommand extends ThetaCommand{
	public function __construct(BasePlugin $plugin){
		parent::__construct($plugin, "getpos", "Get your position", "/gp [player]", ["gp"]);
	}
	public function execute(CommandSender $sender, $commandLabel, array $args){
		if(!($sender instanceof Player)){
			if(!isset($args[0])){
				$this->sendUsage($sender);
				return true;
			}
			$arg = $args[0];
			if(!(($player = $this->getPlugin()->getServer()->getPlayer($arg)) instanceof Player)){
				return $this->notOnline($sender);
			}
			$sender->sendMessage(TextFormat::DARK_AQUA . $player->getName() . TextFormat::GREEN . " is at " . TextFormat::DARK_PURPLE . "($player->x, $player->y, $player->z) ($player->yaw, $player->pitch) @ {$player->getLevel()->getName()}");
		}else{
			$ses = $this->getSession($sender);
			if(!($ses instanceof Session)){
				return true;
			}
			if(!$ses->isModerator()){
				$ses->send(Phrases::CMD_ERR_NO_PERM);
				return true;
			}
			$sender->sendMessage(TextFormat::DARK_AQUA . $sender->getName() . TextFormat::GREEN . " is at " . TextFormat::DARK_PURPLE . "($sender->x, $sender->y, $sender->z) ($sender->yaw, $sender->pitch) @ {$sender->getLevel()->getName()}");
		}
		return true;
	}
}
