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
use legionpe\theta\chat\Hormone;
use legionpe\theta\config\Settings;
use legionpe\theta\lang\Phrases;
use legionpe\theta\Session;
use pocketmine\Server;

class TeamKickQuery extends AsyncQuery{
	/** @var int */
	private $sender;
	/** @var string */
	private $name;
	private $msg;
	public function __construct(BasePlugin $main, Session $sender, $name, $msg){
		$this->sender = $main->storeObject($sender);
		$this->name = $name;
		$this->msg = $msg;
		parent::__construct($main);
	}
	public function getResultType(){
		return self::TYPE_ASSOC;
	}
	public function getQuery(){
		return "SELECT teamrank,uid,tid FROM users WHERE name={$this->esc($this->name)}";
	}
	public function getExpectedColumns(){
		return [
			"uid" => self::COL_INT,
			"tid" => self::COL_INT,
			"teamrank" => self::COL_INT
		];
	}
	public function onCompletion(Server $server){
		$result = $this->getResult();
		if($result["resulttype"] !== self::TYPE_ASSOC){
			return;
		}
		$main = BasePlugin::getInstance($server);
		/** @var Session $sender */
		$sender = $main->fetchObject($this->sender);
		$result = $result["result"];
		if($result["tid"] !== $sender->getTeamId()){
			$sender->send(Phrases::CMD_TEAM_ERR_DIFFERENT_TEAM);
			return;
		}
		if($result["teamrank"] >= $sender->getTeamRank()){
			$sender->send(Phrases::CMD_TEAM_KICK_LOW_TO_HIGH);
			return;
		}
		Hormone::get($main, Hormone::TEAM_KICK_PROPAGANDA, $sender->getInGameName(), $this->msg, Settings::CLASS_ALL, [
			"uid" => $result["uid"]
		])->release();
		new RawAsyncQuery($main, "UPDATE users SET tid=-1, teamrank=0, teamjoin=0, teampts=0 WHERE uid=" . $result["uid"]);
		$sender->send(Phrases::CMD_TEAM_KICK_SUCCESS);
	}
}
