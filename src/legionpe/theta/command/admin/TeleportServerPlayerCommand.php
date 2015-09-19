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

namespace legionpe\theta\command\admin;

use legionpe\theta\BasePlugin;
use legionpe\theta\command\SessionCommand;
use legionpe\theta\query\TeleportServerPlayerQuery;
use legionpe\theta\Session;

class TeleportServerPlayerCommand extends SessionCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "stp", "Teleport to player on another server", "/stp <full name>");
	}
	protected function run(array $args, Session $sender){
		$user = array_shift($args);
		if($user === null){
			return false;
		}
		new TeleportServerPlayerQuery($user, $user, $sender);
		return true;
	}
	protected function checkPerm(Session $session, &$msg = null){
		return $session->isModerator();
	}
}
