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
use legionpe\theta\query\ListFriendsQuery;
use legionpe\theta\Session;

class ListFriendsCommand extends SessionCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "fl", "Friend list", "/fl");
	}
	protected function run(array $args, Session $sender){
		new ListFriendsQuery($this->getPlugin(), $sender);
		return true;
	}
}
