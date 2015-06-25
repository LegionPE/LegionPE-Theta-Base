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

namespace legionpe\theta\command\override;

use legionpe\theta\BasePlugin;
use legionpe\theta\command\ThetaCommand;
use legionpe\theta\lang\Phrases;
use legionpe\theta\Session;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class VersionCommand extends ThetaCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "version", "Shows the server version", "/version", ["ver", "about", "plugins", "about", "pl"]);
	}
	public function execute(CommandSender $sender, $commandLabel, array $args){
		$args = [
			"pmversion" => $this->getPlugin()->getServer()->getName() . " " . $this->getPlugin()->getServer()->getPocketMineVersion(),
			"thetaversion" => $this->getPlugin()->getName(),
			"thetaauthor" => implode(" and ", $this->getPlugin()->getDescription()->getAuthors()),
		];
		if($sender instanceof Player and ($ses = $this->getSession($sender)) instanceof Session){
			$args["lang"] = $ses->translate(Phrases::META_LOCAL);
			$args["langversion"] = $ses->translate(Phrases::META_VERSION);
			$args["langauthor"] = implode(" and ", $ses->translate(Phrases::META_AUTHORS));
			$ses->send(Phrases::CMD_VERSION_MSG, $args);
		}else{
			$msg = "This server uses %pmversion%%info% with %em2%%thetaversion%%info% by %em3%%thetaauthor%%info%.";
			foreach($args as $key => $value){
				$msg = str_replace("%$key%", $value, $msg);
			}
			$sender->sendMessage($msg);
		}
	}
}
