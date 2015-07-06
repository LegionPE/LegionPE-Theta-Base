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

class RemoveFriendCommand extends SessionCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "fr", "Reduce a friend's level (best friend -> good friend -> acquaintance -> not a friend)", "/fr <player>");
	}
	protected function run(array $args, Session $sender){
		if(!isset($args[0])){
			return false;
		}
		$name = array_shift($args);
		$target = $this->getSession($name);
		if(!($target instanceof Session)){
			return $this->notOnline($sender, $name);
		}
		$sender->reduceFriend($target->getUid());
	}
}
