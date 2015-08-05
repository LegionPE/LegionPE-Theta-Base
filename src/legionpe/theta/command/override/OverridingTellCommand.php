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

namespace legionpe\theta\command\override;

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
		$target = $this->getSession($name = array_shift($args));
		if($target === null){
			return $this->offline($sender, $name);
		}
		if(!isset($args[1])){
			return false;
		}
		$message = implode(" ", $args);
		if(!$sender->getSpamDetector()->censor($message)){
			return true;
		}
		$target->getPlayer()->sendMessage($msg = Phrases::VAR_info . "[" . $sender->getPlayer()->getName() . " > " . $target->getPlayer()->getName() . "] " . Phrases::VAR_info . $message);
		$sender->getPlayer()->sendMessage($msg);
		fwrite($this->pmLog, "|from:{$sender->getPlayer()->getName()}|to:{$target->getPlayer()->getName()}|msg:$message|" . PHP_EOL);
		return $msg;
	}
}
