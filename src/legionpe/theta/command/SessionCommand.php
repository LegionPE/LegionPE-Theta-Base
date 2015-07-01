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

use legionpe\theta\lang\Phrases;
use legionpe\theta\Session;
use pocketmine\command\CommandSender;
use pocketmine\event\TextContainer;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

abstract class SessionCommand extends ThetaCommand{
	public function testPermissionSilent(CommandSender $sender){
		if(!($sender instanceof Player)){
			return false;
		}
		$session = $this->getPlugin()->getSession($sender);
		if(!($session instanceof Session)){
			return false;
		}
		return $this->checkPerm($session);
	}
	public function testPermission(CommandSender $sender){
		if(!($sender instanceof Player)){
			$sender->sendMessage(TextFormat::RED . "Please run this command in-game.");
			return false;
		}
		$session = $this->getPlugin()->getSession($sender);
		if(!($session instanceof Session)){
			$sender->sendMessage(Phrases::VAR_wait . "Please run this command later. We are still preparing your account. Sorry for the inconvenience.");
			return false;
		}
		if(!$this->checkPerm($session, $msg)){
			$sender->sendMessage(TextFormat::RED . $msg);
			return false;
		}
		return true;
	}
	public function execute(CommandSender $sender, $l, array $args){
		if(!($sender instanceof Player)){
			$sender->sendMessage(TextFormat::RED . "Please run this command in-game.");
			return true;
		}
		$session = $this->getPlugin()->getSession($sender);
		if(!($session instanceof Session)){
			return true;
		}
		$r = $this->run($args, $this->getPlugin()->getSession($session));
		if($r === false){
			$session->send(Phrases::CMD_ERR_WRONG_USE, ["usage" => $this->getUsage()]);
		}elseif(is_string($r) or ($r instanceof TextContainer)){
			$sender->sendMessage($r);
		}
		return true;
	}
	/**
	 * @param Session $session
	 * @param string $msg
	 * @return bool
	 */
	protected function checkPerm(/** @noinspection PhpUnusedParameterInspection */
		Session $session, &$msg = "You don't have permission to use this command"){
		return true;
	}
	protected abstract function run(array $args, Session $sender);
	protected function offline(Session $sender, $name){
		return $sender->translate(Phrases::CMD_ERR_ABSENT_PLAYER_NAME_KNOWN, ["player" => $name]);
	}
}
