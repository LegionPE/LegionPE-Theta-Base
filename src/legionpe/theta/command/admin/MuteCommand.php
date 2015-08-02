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

use legionpe\theta\Session;
use pocketmine\command\CommandSender;

class MuteCommand extends ModeratorCommand{
	public function execute(CommandSender $sender, $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}
		if(!isset($args[1])){
			return false;
		}
		if(!(($session = $this->getSession($name = array_shift($args))) instanceof Session)){
			return $this->notOnline($sender, $name);
		}
		$length = (int)(floatval(array_shift($args)) * 60);
		$msg = implode(" ", $args);
		/** @noinspection PhpUnusedLocalVariableInspection */
		$mute = $session->mute($msg, $length, $sender->getName());
		$mute->sendToSession($session);
		return true;
	}
}
