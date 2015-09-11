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
use legionpe\theta\query\ListTeamMemberQuery;
use legionpe\theta\Session;

class TeamMemberCommand extends SessionCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "tm", "Show team member list", "/tm [team name]", ["tmem", "tmember", "tmembers"]);
	}
	protected function run(array $args, Session $sender){
		new ListTeamMemberQuery($this->getMain(), isset($args[0]) ? $args[0] : $sender->getTeamName(), $sender);
	}
}
