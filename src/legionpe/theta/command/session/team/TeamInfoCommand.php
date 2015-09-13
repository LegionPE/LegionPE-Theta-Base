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
use legionpe\theta\query\TeamInfoQuery;
use legionpe\theta\Session;

class TeamInfoCommand extends SessionCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "tinfo", "Show a team's information", "/ti [team name]", ["ti"]);
	}
	protected function run(array $args, Session $sender){
		if(isset($args[0])){
			$name = array_shift($args);
		}
		new TeamInfoQuery($sender, isset($name) ? $name : $sender->getTeamName());
		return true;
	}
}
