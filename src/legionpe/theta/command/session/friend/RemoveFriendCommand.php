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
