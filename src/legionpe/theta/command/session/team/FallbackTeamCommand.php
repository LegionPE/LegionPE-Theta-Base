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
use legionpe\theta\Session;

class FallbackTeamCommand extends SessionCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "team", "Help for team commands", "/t", ["t"]);
	}
	protected function run(array $args, Session $sender){
		$em = Phrases::VAR_em;
		$em2 = Phrases::VAR_em2;
		return implode("\n", array_filter([
			"Usage:",
			($sender->isDonator() or $sender->isModerator()) ?
				"$em/tc <team name>:$em2 Create a new team" : "",
			"$em/tj <team name>:$em2 Join an opened team or accept the invitation into the team.",
			"$em/tq:$em2 Quit a team.",
			"$em/tinv <player full name>:$em2 Invite a player into your team.",
			"$em/tprom <player full name>:$em2 Promote a member in your team.",
			"$em/tdem <player full name>:$em2 Demote a member in your team.",
			"$em/tdesc [new description]:$em2 View or edit the team description.",
			"$em/trule [new description]:$em2 View or edit the team rules.",
			"$em/topen:$em2 Open your team to let everyone join it.",
			"$em/tclose:$em2 Set your team as invite-only.",
			"$em/ti [team name]:$em2 Show team information of the specified team or your team.",
			"$em/tm [team name]:$em2 Show members in the specified team or your team",
			"$em/tl:$em2 List the best 5 teams.",
		]));
	}
}
