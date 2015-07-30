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
use legionpe\theta\config\Settings;
use legionpe\theta\lang\Phrases;
use legionpe\theta\query\InviteTeamQuery;
use legionpe\theta\Session;

class TeamInviteCommand extends SessionCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "tinv", "Invite/Accept a player into your team", "/tinv <player full name>");
	}
	protected function run(array $args, Session $sender){
		if($sender->getTeamId() === -1 or $sender->getTeamRank() < Settings::TEAM_RANK_SENIOR){
			return $sender->translate(Phrases::CMD_TEAM_INVITE_NOT_SENIOR);
		}
		$name = array_shift($args);
		if($name === null){
			return false;
		}
		new InviteTeamQuery($this->getMain(), $sender->getTeamId(), $name);
		return true;
	}
}
