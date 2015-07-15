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

class RejectFriendCommand extends SessionCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "fj", "Reject/Cancel a friend request", "/fj <player>");
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
		$orig = $sender->rejectFriend($target->getUid());
		$args = ["from" => $sender->getPlayer()->getDisplayName(), "to" => $target->getPlayer()->getDisplayName()];
		if($orig === Session::FRIEND_IN){
			$sender->send(Phrases::CMD_FRIEND_REJECTED, $args);
			$target->send(Phrases::CMD_FRIEND_REJECTED, $args);
		}elseif($orig === Session::FRIEND_OUT){
			$sender->send(Phrases::CMD_FRIEND_CANCELLED, $args);
			$target->send(Phrases::CMD_FRIEND_CANCELLED, $args);
		}else{
			$sender->send(Phrases::CMD_FRIEND_NO_INVITATION, $args);
		}
		return true;
	}
}
