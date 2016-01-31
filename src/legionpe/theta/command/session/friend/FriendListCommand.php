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
use legionpe\theta\Session;

class FriendListCommand extends SessionCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "flist", "Show friend/enemy inbox, outbox and list", "/fl", ["fl", "friends"]);
	}

	protected function run(array $args, Session $sender){
		$gt = "";
		/**
		 * @var int      $type
		 * @var Friend[] $friends
		 */
		/*foreach($sender->getLoginDatum("friends") as $type => $friends){
			if($type === Friend::FRIEND_ACQUAINTANCE or $type === Friend::FRIEND_ENEMY or $type === Friend::FRIEND_BEST_FRIEND){
				continue;
			}
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
		}*/
		$in = [];
		$out = [];
		$now = [];
		/** @type Friend $friend */
		foreach(array_merge(...$sender->getLoginDatum("friends")) as $friend){
			if($friend->type === Friend::FRIEND_NOT_FRIEND){
				if($friend->getRequestRelativeDirection() === Friend::DIRECTION_OUT){
					$name = $friend->friendName;
					if($this->getMain()->getSessionByUid($friend->friendUid) instanceof Session){
						$name .= Phrases::VAR_em2 . "*" . Phrases::VAR_info;
					}
					$out[] = $name;
				}
				if($friend->getRequestRelativeDirection() === Friend::DIRECTION_IN){
					$name = $friend->friendName;
					if($this->getMain()->getSessionByUid($friend->friendUid) instanceof Session){
						$name .= Phrases::VAR_em2 . "*" . Phrases::VAR_info;
					}
					$in[] = $name;
				}
			}elseif($friend->type === Friend::FRIEND_GOOD_FRIEND){
				$name = $friend->friendName;
				if($this->getMain()->getSessionByUid($friend->friendUid) instanceof Session){
					$name .= Phrases::VAR_em2 . "*" . Phrases::VAR_info;
				}
				$now[] = $name;
			}
		}
		$gt .= Phrases::VAR_em . $sender->translate(Phrases::FRIEND_DIR_NOW) . ": " . Phrases::VAR_info;
		$gt .= implode(", ", $now);
		$gt .= Phrases::VAR_em . $sender->translate(Phrases::FRIEND_DIR_IN) . ": " . Phrases::VAR_info;
		$gt .= implode(", ", $in);
		$gt .= Phrases::VAR_em . $sender->translate(Phrases::FRIEND_DIR_OUT) . ": " . Phrases::VAR_info;
		$gt .= implode(", ", $out);
		return $gt;
	}
}
