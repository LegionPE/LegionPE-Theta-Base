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

namespace legionpe\theta\command\session;

use legionpe\theta\BasePlugin;
use legionpe\theta\command\SessionCommand;
use legionpe\theta\Session;
use pocketmine\block\Block;
use pocketmine\utils\TextFormat;

class DirectTeleportCommand extends SessionCommand{
	public function __construct(BasePlugin $plugin){
		parent::__construct($plugin, "dtp", "Alternative LegionPE style teleportation", "/dtp [teleporter = me] <target[-<blocks behind target>]> [-f (force teleport multi-world)]");
	}
	protected function run(array $args, Session $sender){
		if(!isset($args[0])){
			return false;
		}
		if(isset($args[1])){
			$f = array_shift($args);
			$from = $this->getSession($f);
			if(!($from instanceof Session)){
				return $this->notOnline($f);
			}
		}else{
			$from = $sender;
		}
		$arg2 = array_shift($args);
		if(($pos = strpos($arg2, "-")) !== false){
			$toName = substr($arg2, $pos);
			$distance = -((int) substr($arg2, $pos + 1));
		}else{
			$toName = $arg2;
			$distance = 0;
		}
		$to = $this->getSession($toName);
		if(!($to instanceof Session)){
			return $this->notOnline($toName);
		}
		$force = false;
		while(isset($args[0])){
			$arg = array_shift($args);
			if($arg === "-f"){
				$force = true;
			}
		}
		$player = $to->getPlayer();
		$vector = $player->getDirectionVector();
		$delta = $vector->multiply($distance);
		$target = $player->add($delta);
		$level = $player->getLevel();
		$target->y = $target->getFloorY();
		for($block = $level->getBlock($target); $block->y < 128; $block = $level->getBlock($target = $target->add(0, 1))){
			if($block->getId() === Block::AIR or (Block::WATER <= $block->getId() and $block->getId() <= Block::STILL_LAVA)){
				break;
			}
		}
		if(!$force and $from->getPlayer()->getLevel()->getName() !== $level->getName()){
			return TextFormat::YELLOW . "Teleport multi-world (to " . $to->getPlayer()->getLevel()->getName() . ") with the -f option";
		}
		$from->getPlayer()->teleport($target);
		return TextFormat::GREEN . "Teleported to $distance blocks behind $to: $target->x, $target->y, $target->z @ {$level->getName()}";
	}
	protected function checkPerm(Session $session, &$msg = ""){
		if(!$session->isModerator()){
			$msg = "This command is only for moderators.";
			return false;
		}
		return true;
	}
}
