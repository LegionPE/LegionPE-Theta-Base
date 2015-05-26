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
