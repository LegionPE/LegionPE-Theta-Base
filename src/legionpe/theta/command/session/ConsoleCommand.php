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
use legionpe\theta\chat\ConsoleChatType;
use legionpe\theta\command\SessionCommand;
use legionpe\theta\config\Settings;
use legionpe\theta\Session;

class ConsoleCommand extends SessionCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "console", "Send a message to console", "/console <message ...>", ["cs"]);
	}
	protected function run(array $args, Session $sender){
		if(!isset($args[0])){
			return false;
		}
		$msg = implode(" ", $args);
		$local = true;
		if(substr($msg, 0, 1) === "."){
			$msg = substr($msg, 1);
			$local = false;
		}
		$type = new ConsoleChatType($sender->getMain(), $sender->getInGameName(), $msg, $local ? Settings::$LOCALIZE_CLASS : Settings::CLASS_ALL, []);
		$type->push();
		return true;
	}
}
