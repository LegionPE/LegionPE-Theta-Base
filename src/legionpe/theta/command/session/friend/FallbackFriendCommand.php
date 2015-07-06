<?php

/**
 * Theta
 * Copyright (C) 2015 PEMapModder
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace legionpe\theta\command\session\friend;

use legionpe\theta\BasePlugin;
use legionpe\theta\command\SessionCommand;
use legionpe\theta\Session;

class FallbackFriendCommand extends SessionCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "friends", "Show help for friend commands", "/f", [
			"f",
			"friend"
		]);
	}
	protected function run(array $args, Session $sender){
		return "Usage:\n/fa <player>: Invite (or accept invitation if invited) target player to raise friend level\n/fr <player>: Reduce the friend level with the target player\n/fj <player>: Reject invitation from target player\n/fl: View friend list\nYou need to raise another player to a 'good friend' to avoid being attacked by him"; // TODO translate
	}
}
