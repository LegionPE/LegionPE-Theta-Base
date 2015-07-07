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

namespace legionpe\theta\command\session;

use legionpe\theta\BasePlugin;
use legionpe\theta\command\SessionCommand;
use legionpe\theta\lang\Phrases;
use legionpe\theta\Session;

class OverridingTellCommand extends SessionCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "tell", "Send a private message to another player", "/w <player> <message>", ["w", "msg", "pm"]);
		touch($main->getDataFolder() . "privmsg.log");
		$this->pmLog = fopen($main->getDataFolder() . "privmsg.log", "at");
	}
	public function __destruct(){
		fclose($this->pmLog);
	}
	protected function run(array $args, Session $sender){
		$sender->send(Phrases::CMD_PRIV_MSG_REMIND_QUERY);
		if(!isset($args[1])){
			return false;
		}
		$target = $this->getSession($name = array_shift($args));
		if($target === null){
			return $this->offline($sender, $name);
		}
		$target->getPlayer()->sendMessage($msg = Phrases::VAR_info . "[" . $sender->getPlayer()->getName() . " > " . $target->getPlayer()->getName() . "] " . Phrases::VAR_info . ($message = implode(" ", $args)));
		$sender->getPlayer()->sendMessage($msg);
		fwrite($this->pmLog, "|from:{$sender->getPlayer()->getName()}|to:{$target->getPlayer()->getName()}|msg:$message|" . PHP_EOL);
		return $msg;
	}
}
