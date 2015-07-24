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

namespace legionpe\theta\command\session\friend;

use legionpe\theta\BasePlugin;
use legionpe\theta\command\SessionCommand;
use legionpe\theta\Session;

class FallbackFriendCommand extends SessionCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "friends", "Show help for friend commands", "/f", [
			"f",
			"friend",
		]);
	}
	protected function run(array $args, Session $sender){
		return "Usage:\n/fa <player>: Invite (or accept invitation if invited) target player to raise friend level\n/fr <player>: Reduce the friend level with the target player\n/fj <player>: Reject invitation from target player\n/fl: View friend list\nYou need to raise another player to a 'good friend' to avoid being attacked by him"; // TODO translate
	}
}
