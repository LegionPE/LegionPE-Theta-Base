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
use legionpe\theta\lang\Phrases;
use legionpe\theta\Session;

class AddFriendCommand extends SessionCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "fa", "Accept or send invitation to another player to increase friend level", "/fa <player>");
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
		$phrase = $sender->inviteIncrease($target->getUid(), $target->getPlayer()->getDisplayName(), $vars);
		if($phrase === Phrases::CMD_FRIEND_RAISED){
			$target->send($phrase, ["target" => $sender->getPlayer()->getDisplayName(), "newtype" => $vars["newtype"]]);
		}elseif($phrase === Phrases::CMD_FRIEND_RAISE_REQUESTED){
			$target->send(Phrases::CMD_FRIEND_RECEIVED, ["source" => $sender->getPlayer()->getDisplayName(), "newtype" => $vars["newtype"]]);
		}
		return $sender->translate($phrase, $vars);
	}
}
