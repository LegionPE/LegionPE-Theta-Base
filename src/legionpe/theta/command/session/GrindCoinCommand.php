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
use legionpe\theta\command\SessionCommand;
use legionpe\theta\config\Settings;
use legionpe\theta\lang\Phrases;
use legionpe\theta\Session;
use legionpe\theta\utils\MUtils;

class GrindCoinCommand extends SessionCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "coingrind", "Enable coin grinding", "/cg", ["grindcoin", "cg", "gc"]);
	}
	protected function run(array $args, Session $sender){
		$lbl = func_get_arg(2);
		if(is_string($lbl) and $lbl !== "cg"){
			$sender->send(Phrases::CMD_GRIND_COIN_ADVICE);
		}
		if(!$sender->canStartGrind()){
			return $sender->translate(Phrases::CMD_GRIND_COIN_CANNOT_START, ["time" => MUtils::time_secsToString($sender->getGrindWaitTime())]);
		}
		if(!$sender->confirmGrind){
			$sender->confirmGrind = true;
			return $sender->translate(Phrases::CMD_GRIND_COIN_REQUEST_CONFIRM, [
				"length" => MUtils::time_secsToString(Settings::getGrindLength($sender->getRank())),
				"amplitude" => Settings::getGrindFactor($sender->getRank())
			]);
		}
		$sender->confirmGrind = false;
		$sender->startGrinding();
		return $sender->translate(Phrases::CMD_GRIND_COIN_STARTED);
	}
	protected function checkPerm(Session $session, &$msg = null){
		if($session->isDonator()){
			return true;
		}
		$msg = $session->translate(Phrases::CMD_ERR_NO_PERM_DONATE);
		return false;
	}
}
