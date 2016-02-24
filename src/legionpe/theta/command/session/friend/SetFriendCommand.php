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
use legionpe\theta\query\SetFriendQuery;
use legionpe\theta\Session;

class SetFriendCommand extends SessionCommand{
	public static $CMD_NAMES = [
		Friend::FRIEND_ENEMY => "enemy",
		Friend::FRIEND_NOT_FRIEND => "norm",
		Friend::FRIEND_ACQUAINTANCE => "acq",
		Friend::FRIEND_GOOD_FRIEND => "gf",
		Friend::FRIEND_BEST_FRIEND => "bf",
	];
	public static $HUMAN_NAMES = [
		Friend::FRIEND_ENEMY => "enemy",
		Friend::FRIEND_NOT_FRIEND => "no relationship",
		Friend::FRIEND_ACQUAINTANCE => "acquaintance",
		Friend::FRIEND_GOOD_FRIEND => "good friend",
		Friend::FRIEND_BEST_FRIEND => "best friend",
	];
	private $cmdName, $humanName;
	/** @var int */
	private $level;

	public function __construct(BasePlugin $main, $level, $aliases = []){
		$this->level = $level;
		$this->cmdName = self::$CMD_NAMES[$level];
		$this->humanName = self::$HUMAN_NAMES[$level];
		parent::__construct($main, $this->cmdName, "Set a player to be a $this->humanName", "/$this->cmdName <player>", $aliases);
	}

	protected function run(array $args, Session $sender){
		if(!isset($args[0])){
			return false;
		}
		$name = array_shift($args);
		if(strtolower($name) === strtolower($sender->getPlayer()->getName())){
			return false;
		}
		new SetFriendQuery($this->getMain(), $name, $sender->getUid(), $this->level);
		return true;
	}
}
