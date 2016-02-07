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

namespace legionpe\theta\command\session;

use legionpe\theta\BasePlugin;
use legionpe\theta\command\SessionCommand;
use legionpe\theta\miscellaneous\walkingparticle\WalkingParticle;
use legionpe\theta\Session;
use pocketmine\block\Block;
use pocketmine\utils\TextFormat;

class SpectatingCommand extends SessionCommand{
	public function __construct(BasePlugin $plugin){
		parent::__construct($plugin, "mode", "Change mode", "/mode <normal|spectate>");
	}
	protected function run(array $args, Session $sender){
		if(!isset($args[0])){
			return false;
		}
		switch($args[0]){
			case "normal":
				$sender->setMode(Session::MODE_NORMAL);
				break;
			case "spectate":
				$sender->setMode(Session::MODE_SPECTATING);
				break;
		}
	}
	protected function checkPerm(Session $session, &$msg = ""){
		if(!$session->isDonator()){
			$msg = "This command is only for donators.";
			return false;
		}
		return true;
	}
}
