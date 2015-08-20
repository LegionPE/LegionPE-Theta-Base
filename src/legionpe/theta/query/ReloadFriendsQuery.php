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

namespace legionpe\theta\query;

use legionpe\theta\BasePlugin;
use legionpe\theta\Friend;
use legionpe\theta\Session;
use pocketmine\Server;

class ReloadFriendsQuery extends AsyncQuery{
	/** @var int */
	private $uid;
	public function __construct(BasePlugin $main, $uid){
		$this->uid = $uid;
		parent::__construct($main);
	}
	public function getResultType(){
		return self::TYPE_ALL;
	}
	public function getQuery(){
		return "SELECT IF(smalluid=$this->uid, largeuid, smalluid)AS uid, type,requested,direction,(SELECT name FROM users WHERE uid=IF(friends.smalluid=$this->uid, friends.largeuid, friends.smalluid)) FROM friends WHERE smalluid=$this->uid OR largeuid=$this->uid";
	}
	public function getExpectedColumns(){
		return [
			"uid" => self::COL_INT,
			"type" => self::COL_INT,
			"requested" => self::COL_INT,
			"direction" => self::COL_INT,
			"name" => self::COL_STRING,
		];
	}
	public function onCompletion(Server $server){
		$main = BasePlugin::getInstance($server);
		$ses = $main->getSessionByUid($this->uid);
		if($ses instanceof Session){
			$friends = [
				Friend::FRIEND_ENEMY => [],
				Friend::FRIEND_ACQUAINTANCE => [],
				Friend::FRIEND_GOOD_FRIEND => [],
				Friend::FRIEND_BEST_FRIEND => [],
			];
			foreach($this->getResult()["result"] as $friend){
				$friendUid = $friend["uid"];
				$type = $friend["type"];
				$requested = $friend["requested"];
				$reqDir = $friend["direction"];
				$name = $friend["name"];
				$friends[$type][$friendUid] = new Friend($this->uid, $friendUid, $type, $requested, $reqDir, $name);
			}
			$ses->setLoginDatum("friends", $friends);
		}
	}
}
