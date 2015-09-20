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
use legionpe\theta\lang\Phrases;
use legionpe\theta\Session;
use pocketmine\Server;

class TeamInfoQuery extends AsyncQuery{
	/** @var int<Session> */
	private $sender;
	/** @var string */
	private $name;
	public function __construct(Session $sender, $name){
		$this->sender = $sender->getMain()->storeObject($sender);
		$this->name = $name;
		parent::__construct($sender->getMain());
	}
	public function getResultType(){
		return self::TYPE_ASSOC;
	}
	public function getQuery(){
		return "SELECT teams.name AS name, teams.descr AS descr, teams.rules AS rules, teams.req AS req, SUM(users.teampts) + teams.points AS points, SUM(pvp_kills) AS pvp_kills, SUM(pvp_deaths) AS pvp_deaths, AVG(pvp_maxstreak) AS pvp_avgstreak, COUNT(users.tid) AS memscnt FROM teams INNER JOIN users ON teams.tid=users.tid WHERE teams.name={$this->esc($this->name)}";
	}
	public function getExpectedColumns(){
		return [
			"name" => self::COL_STRING,
			"descr" => self::COL_STRING,
			"rules" => self::COL_STRING,
			"req" => self::COL_STRING,
			"points" => self::COL_FLOAT,
			"pvp_kills" => self::COL_INT,
			"pvp_deaths" => self::COL_INT,
			"pvp_avgstreak" => self::COL_INT,
			"memscnt" => self::COL_INT,
		];
	}
	public function onCompletion(Server $server){
		/** @var Session $sender */
		$sender = BasePlugin::getInstance($server)->fetchObject($this->sender);
		if(!$sender->getPlayer()->isOnline()){
			return;
		}
		$result = $this->getResult();
		if($result["resulttype"] !== self::TYPE_ASSOC){
			$sender->send(Phrases::CMD_TEAM_ERR_NO_SUCH_TEAM, ["name" => $this->name]);
			return;
		}
		$row = $result["result"];
		if($row["pvp_deaths"] === 0){
			$row["pvp_kd"] = "N/A";
		}else{
			$row["pvp_kd"] = round($row["pvp_kills"] / $row["pvp_deaths"], 3);
		}
		$sender->send(Phrases::CMD_TEAM_INFO_RESULT, $row);
	}
}
