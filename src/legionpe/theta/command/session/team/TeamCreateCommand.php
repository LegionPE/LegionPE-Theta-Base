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

namespace legionpe\theta\command\session\team;

use legionpe\theta\BasePlugin;
use legionpe\theta\command\SessionCommand;
use legionpe\theta\lang\Phrases;
use legionpe\theta\query\CreateTeamQuery;
use legionpe\theta\Session;

class TeamCreateCommand extends SessionCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "tc", "Create a new team", "/tc <team name>");
	}
	protected function checkPerm(Session $session, &$msg = null){
		if(!$session->isModerator() and !$session->isDonator()){
			$msg = $session->translate(Phrases::CMD_ERR_NO_PERM_DONATE);
			return false;
		}
		return true;
	}
	protected function run(array $args, Session $sender){
		if($sender->getTeamId() !== -1){
			return $sender->translate(Phrases::CMD_TEAM_ERR_ALREADY_IN_TEAM);
		}
		$name = array_shift($args);
		if($name === null){
			return false;
		}
		if(!preg_match('/^[A-Za-z0-9_]{5,}$/', $name)){
			$sender->send(Phrases::CMD_TEAM_CREATE_INVALID_NAME);
			return true;
		}
		new CreateTeamQuery($this->getMain(), $sender->getUid(), $name);
		return $sender->translate(Phrases::CMD_TEAM_LOADING);
	}
}
