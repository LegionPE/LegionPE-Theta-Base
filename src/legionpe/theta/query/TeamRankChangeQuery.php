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

class TeamRankChangeQuery extends AsyncQuery{
	/** @var bool */
	private $promote;
	private $name;
	/** @var int */
	private $sender;
	public function __construct(BasePlugin $main, $promote, $name, Session $sender){
		$this->promote = $promote;
		$this->name = $name;
		$this->sender = $main->storeObject($sender);
		parent::__construct($main);
	}
	public function getQuery(){
		return "SELECT uid,tid,teamrank FROM users WHERE name={$this->esc($this->name)}";
	}
	public function getResultType(){
		return self::TYPE_ASSOC;
	}
	public function getExpectedColumns(){
		return [
			"uid" => self::COL_INT,
			"tid" => self::COL_INT,
			"teamrank" => self::COL_INT
		];
	}
	public function onCompletion(Server $server){
		$main = BasePlugin::getInstance($server);
		$sender = $main->fetchObject($this->sender);
		$result = $this->getResult();
		if($sender->getPlayer() === null){
			return;
		}
		if($result["resulttype"] !== self::TYPE_ASSOC){
			$sender->send(Phrases::CMD_ERR_NOT_FOUND, ["name" => $this->name]);
			return;
		}
		$result = $result["result"];
		$teamrank = $result["teamrank"];
		$tid = $result["tid"];
		if($tid !== $sender->getTeamId()){
			$sender->send(Phrases::CMD_TEAM_ERR_DIFFERENT_TEAM);
			return;
		}
		if($sender->getTeamRank() <= $teamrank){
			$sender->send(Phrases::CMD_TEAM_RANK_CHANGE_NEED_TO_LOW);
			return;
		}
		if($teamrank === Settings::TEAM_RANK_JUNIOR and !$this->promote){
			$sender->send(Phrases::CMD_TEAM_RANK_CHANGE_NO_DEMOTE_JUNIOR);
			return;
		}
		if($teamrank === Settings::TEAM_RANK_COLEAD and $this->promote){
			$sender->send(Phrases::CMD_TEAM_RANK_CHANGE_NO_PROMOTE_CO_LEADER);
			return;
		}
		if($teamrank === Settings::TEAM_RANK_LEADER){
			$sender->send(Phrases::CMD_TEAM_RANK_CHANGE_ONE_LEADER);
			return;
		}
		if($this->promote){
			$teamrank++;
			$sender->translate(Phrases::CMD_TEAM_RANK_CHANGE_PROMOTED, [
				"name" => $this->name,
				"newrank" => $sender->translate(Phrases::WORDS_TEAM_RANKS)[$teamrank]
			]);
		}else{
			$teamrank--;
			$sender->translate(Phrases::CMD_TEAM_RANK_CHANGE_PROMOTED, [
				"name" => $this->name,
				"newrank" => $sender->translate(Phrases::WORDS_TEAM_RANKS)[$teamrank]
			]);
		}
	}
}
