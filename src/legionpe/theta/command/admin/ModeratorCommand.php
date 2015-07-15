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

namespace legionpe\theta\command\admin;

use legionpe\theta\command\ThetaCommand;
use legionpe\theta\Session;
use pocketmine\command\CommandSender;
use pocketmine\Player;

abstract class ModeratorCommand extends ThetaCommand{
	public function testPermissionSilent(CommandSender $sender){
		if($sender instanceof Player){
			$ses = $this->getSession($sender);
			if(!($ses instanceof Session)){
				return false;
			}
			return $this->hasPermission($ses);
		}
		return true;
	}
	protected function hasPermission(Session $session){
		return $session->isModerator();
	}
	public function staffBroadcast($msg){
		foreach($this->getPlugin()->getSessions() as $ses){
			if($ses->isModerator()){
				$ses->getPlayer()->sendMessage($msg);
			}
		}
	}
}
