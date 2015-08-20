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
use legionpe\theta\command\SessionCommand;
use legionpe\theta\Friend;
use legionpe\theta\lang\Phrases;
use legionpe\theta\query\SetFriendQuery;
use legionpe\theta\Session;

class FriendListCommand extends SessionCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "flist", "Show friend/enemy inbox, outbox and list", "/fl", ["fl"]);
	}
	protected function run(array $args, Session $sender){
		$gt = "";
		/**
		 * @var int $type
		 * @var Friend[] $friends
		 */
		foreach($sender->getLoginDatum("friends") as $type => $friends){
			$out = Phrases::VAR_info . ucfirst(SetFriendQuery::$TYPES[$type]) . ":  ";
			foreach($friends as $friend){
				if($this->getPlugin()->getSessionByUid($friend->friendUid) instanceof Session){
					$out .= Phrases::VAR_em . "*" . Phrases::VAR_info;
				}
				$out .= $friend->friendName;
				if($friend->getRequestRelativeDirection() === Friend::DIRECTION_OUT){
					$out .= Phrases::VAR_em3;
					$out .= "<-";
					$out .= SetFriendQuery::$TYPES[$friend->requestedType];
					$out .= Phrases::VAR_info;
				}elseif($friend->getRequestRelativeDirection() === Friend::DIRECTION_IN){
					$out .= Phrases::VAR_em2;
					$out .= "->";
					$out .= SetFriendQuery::$TYPES[$friend->requestedType];
					$out .= Phrases::VAR_info;
				}
				$out .= ", ";
			}
			$gt .= substr($out, 0, -2) . "\n";
		}
		return $gt;
	}
}
