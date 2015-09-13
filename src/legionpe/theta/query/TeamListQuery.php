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

namespace legionpe\theta\query;

use legionpe\theta\BasePlugin;
use legionpe\theta\Session;
use pocketmine\Server;

class TeamListQuery extends AsyncQuery{
	/** @var int<Session> */
	private $sender;
	public function __construct(BasePlugin $main, Session $sender){
		$this->sender = $main->storeObject($sender);
		parent::__construct($main);
	}
	public function getResultType(){
		return self::TYPE_ALL;
	}
	public function getQuery(){
		return "SELECT name, points + (SELECT SUM(teampts) FROM users WHERE tid=teams.tid) AS total_points FROM teams ORDER BY total_points LIMIT 5";
	}
	public function getExpectedColumns(){
		return [
			"name" => self::COL_STRING,
			"total_points" => self::COL_INT
		];
	}
	public function onCompletion(Server $server){
		/** @var Session $sender */
		$sender = BasePlugin::getInstance($server)->fetchObject($this->sender);
		if(!$sender->getPlayer()->isOnline()){
			return;
		}
		$r = $this->getResult();
		if($r["resulttype"] !== self::TYPE_ALL){
			return;
		}
		foreach($r["result"] as $i => $row){
			$num = $i + 1;
			$sender->getPlayer()->sendMessage("#$num) " . $row["name"] . " (" . $row["total_points"] . " points)"); // TODO translate
		}
	}
}
