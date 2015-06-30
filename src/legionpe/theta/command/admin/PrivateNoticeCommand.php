<?php

/**
 * Theta
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

use legionpe\theta\BasePlugin;
use legionpe\theta\lang\Phrases;
use legionpe\theta\Session;
use pocketmine\command\CommandSender;

class PrivateNoticeCommand extends ModeratorCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "pn", "Send a private notice to a player", "/pn <player> <message ...>");
	}
	public function hasPermsision(Session $session){
		return $session->isAdmin();
	}
	public function execute(CommandSender $sender, $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return;
		}
		if(!isset($args[1])){
			$this->sendUsage($sender);
			return;
		}
		$name = array_shift($args);
		$ses = $this->getSession($name);
		if($ses === null){
			$this->notOnline($sender, $name);
			return;
		}
		$ses->send(Phrases::CMD_PRIV_NOTICE_RECIPIENT, ["msg" => $msg = implode(" ", $args)]);
		$sender->sendMessage("PN to $ses: $msg");
	}
}
