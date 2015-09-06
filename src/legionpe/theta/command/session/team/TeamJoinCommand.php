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
use legionpe\theta\query\JoinTeamQuery;
use legionpe\theta\Session;

class TeamJoinCommand extends SessionCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "tj", "Join, send an invitation request to or accept invitation from a team", "/tj <target team>", []);
	}
	protected function run(array $args, Session $sender){
		if($sender->getTeamId() !== -1){
			return $sender->translate(Phrases::CMD_TEAM_ERR_ALREADY_IN_TEAM);
		}
		if(!isset($args[0])){
			return false;
		}
		$team = array_shift($args);
		new JoinTeamQuery($this->getMain(), $sender, $team);
		return $sender->translate(Phrases::CMD_TEAM_LOADING);
	}
}
