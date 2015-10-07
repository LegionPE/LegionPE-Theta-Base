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
		return implode("\n", [
			"=== Usage: ===",
			"/enemy, /norm, /acq, /gf, /bf: Set friends",
			"Enemies cannot teleport-request away or to players 5 blocks near you.",
			"Only Acquaintances (acq), Good Friends (gf) and Best Friends (bf) can view your stats if you set your stats to private.",
			"Good Friends (gf) and Best Friends (bf) cannot attack each other unless they have Friendly Fire Activated (ffa).",
			"Best Friends (bf) can teleport to each other or teleport each other to themselves without the other one accepting."
		]);
	}
}
