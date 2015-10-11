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
use legionpe\theta\config\Settings;
use legionpe\theta\lang\Phrases;
use legionpe\theta\Session;
use pocketmine\Server;

class ListTeamMemberQuery extends AsyncQuery{
	/** @var string */
	private $name;
	private $sender;
	public function __construct(BasePlugin $main, $name, Session $sender){
		$this->name = (string) $name;
		$this->sender = $main->storeObject($sender);
		parent::__construct($main);
	}
	public function getResultType(){
		return self::TYPE_ALL;
	}
	public function getExpectedColumns(){
		return [
			"teamrank" => self::COL_INT,
			"nicks" => self::COL_STRING
		];
	}
	public function getQuery(){
		return "SELECT teamrank, nicks FROM users WHERE tid=(SELECT tid FROM teams WHERE name={$this->esc($this->name)}) ORDER BY teamrank DESC, teamjoin ASC";
	}
	public function onCompletion(Server $server){
		$main = BasePlugin::getInstance($server);
		/** @var Session $sender */
		$sender = $main->fetchObject($this->sender);
		if(!$sender->getPlayer()->isOnline()){
			return;
		}
		$members = [
			Settings::TEAM_RANK_LEADER => [],
			Settings::TEAM_RANK_COLEAD => [],
			Settings::TEAM_RANK_SENIOR => [],
			Settings::TEAM_RANK_MEMBER => [],
			Settings::TEAM_RANK_JUNIOR => [],
		];
		$result = $this->getResult();
		if($result["resulttype"] !== self::TYPE_ALL){
			return;
		}
		if(count($result["result"]) === 0){
			$sender->send(Phrases::CMD_TEAM_ERR_NO_SUCH_TEAM, ["name" => $this->name]);
			return;
		}
		foreach($result["result"] as $row){
			$nicks = trim($row["nicks"], "|");
			$nick = explode("|", $nicks)[0];
			$members[$row["teamrank"]][] = $nick;
		}
		$sender->send(Phrases::CMD_TEAM_MEMBER_RESULT, [
			"leader" => implode(", ", $members[Settings::TEAM_RANK_LEADER]),
			"coleaders" => implode(", ", $members[Settings::TEAM_RANK_COLEAD]),
			"seniors" => implode(", ", $members[Settings::TEAM_RANK_SENIOR]),
			"members" => implode(", ", $members[Settings::TEAM_RANK_MEMBER]),
			"juniors" => implode(", ", $members[Settings::TEAM_RANK_JUNIOR]),
		]);
	}
}
