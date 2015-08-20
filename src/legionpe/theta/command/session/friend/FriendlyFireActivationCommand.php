<?php

/*
 * Theta
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
use legionpe\theta\classic\ClassicSession;
use legionpe\theta\command\SessionCommand;
use legionpe\theta\lang\Phrases;
use legionpe\theta\Session;

class FriendlyFireActivationCommand extends SessionCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "ffa", "Friendly Fire Activation", "/ffa [on|off]", ["ff"]);
	}
	/**
	 * @param array $args
	 * @param ClassicSession|Session $sender
	 * @return string
	 */
	protected function run(array $args, Session $sender){
		if(isset($args[0])){
			if($args[0] === "on"){
				$on = true;
			}elseif($args[0] === "off"){
				$on = false;
			}
		}
		if(!isset($on)){
			return $sender->translate($sender->isFriendlyFireActivated() ? Phrases::CMD_FFA_CHECK_TRUE : Phrases::CMD_FFA_CHECK_FALSE);
		}
		$sender->setFriendlyFireActivated($on);
		return $sender->translate($on ? Phrases::CMD_FFA_SET_TRUE : Phrases::CMD_FFA_SET_FALSE);
	}
}
