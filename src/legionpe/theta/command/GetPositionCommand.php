<?php

/**
 * LegionPE
 * Copyright (C) 2015 PEMapModder
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace legionpe\theta\command;

use legionpe\theta\BasePlugin;
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
			$sender->sendMessage(TextFormat::DARK_AQUA . $player->getName() . TextFormat::GREEN . " is at " . TextFormat::DARK_PURPLE . "$player->x, $player->y, $player->z @ {$player->getLevel()->getName()}");
		}else{
			$sender->sendMessage(TextFormat::DARK_AQUA . $sender->getName() . TextFormat::GREEN . " is at " . TextFormat::DARK_PURPLE . "$sender->x, $sender->y, $sender->z @ {$sender->getLevel()->getName()}");
		}
		return true;
	}
}
