<?php

/*
 * LegionPE
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
use legionpe\theta\config\Settings;
use legionpe\theta\lang\Phrases;
use legionpe\theta\query\TeamKickQuery;
use legionpe\theta\Session;

class TeamKickCommand extends SessionCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "tkick", "Kick a player from the team", "/tk <full name> [message]", ["tk"]);
	}
	protected function run(array $args, Session $sender){
		if(!isset($args[0])){
			return false;
		}
		if($sender->getTeamRank() < Settings::TEAM_RANK_COLEAD){
			return $sender->translate(Phrases::CMD_TEAM_KICK_COLEAD);
		}
		new TeamKickQuery($this->getMain(), $sender, array_shift($args), implode(" ", $args));
		return true;
	}
}
