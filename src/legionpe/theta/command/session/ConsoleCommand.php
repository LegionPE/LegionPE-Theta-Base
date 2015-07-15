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
		$type = new ConsoleChatType($sender->getMain(), $sender->getInGameName(), $msg, $local ? Settings::$LOCALIZE_CLASS : Settings::CLASS_ALL, ["ip" => Settings::$LOCALIZE_IP, "port" => Settings::$LOCALIZE_PORT]);
		$type->push();
		return true;
	}
}
