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

namespace legionpe\theta\query;

use legionpe\theta\BasePlugin;
use legionpe\theta\chat\ChatType;
use legionpe\theta\config\Settings;
use legionpe\theta\lang\Phrases;
use pocketmine\Server;

class JoinTeamQuery extends AsyncQuery{
	const REQUEST_FROM_TEAM = 1;
	const REQUEST_FROM_USER = 2;
	const DIRECT_JOIN = 3;
	/** @var int */
	private $uid;
	/** @var string */
	private $teamName;
	/** @var int */
	private $tid;
	/** @var int */
	private $type;
	public function __construct(BasePlugin $main, $uid, $teamName){
		$this->teamName = $teamName;
		$this->uid = $uid;
		parent::__construct($main);
	}
	public function getResultType(){
		return self::TYPE_ASSOC;
	}
	public function onPreQuery(\mysqli $db){
		$result = $db->query("SELECT tid, config, (SELECT type FROM tjrequests WHERE tid=teams.tid AND user=$this->uid) AS type FROM teams WHERE name={$this->esc($this->teamName)}");
		$checkFirst = $result->fetch_assoc();
		if(!is_array($checkFirst)){
			throw new \RuntimeException(Phrases::VAR_error . "Team does not exist");
		}
		$this->tid = $result["tid"];
		$this->type = $result["type"];
		$config = $result["config"];
		$result->close();
		if($config & Settings::TEAM_CONFIG_OPEN){
			$this->type = self::DIRECT_JOIN;
			throw new \RuntimeException(Phrases::CMD_TEAM_JOIN_DIRECTLY_JOINED);
		}
		if($this->type === self::REQUEST_FROM_USER){
			throw new \RuntimeException(Phrases::VAR_error . "You have already sent a request to join the team.");
		}
	}
	public function getQuery(){
		$fromUser = self::REQUEST_FROM_USER;
		return ($this->type === self::REQUEST_FROM_TEAM) ? "DELETE FROM tjrequests WHERE tid=$this->tid AND user=$this->uid" : "INSERT INTO tjrequests (team, user, type) VALUES ($this->tid, $this->uid, $fromUser)";
	}
	public function onCompletion(Server $server){
		$main = BasePlugin::getInstance($server);
		$data = ["teamname" => $this->teamName];
		foreach($main->getSessions() as $ses){
			if($ses->getUid() === $this->uid){
				$data["name"] = $ses->getPlayer()->getName();
				switch($this->type){
					case self::REQUEST_FROM_USER:
						$ses->send(Phrases::CMD_TEAM_JOIN_ALREADY_REQUESTED, $data);
						break 2;
					case self::REQUEST_FROM_TEAM:
						$ses->send(Phrases::CMD_TEAM_JOIN_ACCEPTED, $data);
						$joined = true;
						$ses->recalculateNametag();
						break 2;
					case self::DIRECT_JOIN:
						$ses->send(Phrases::CMD_TEAM_JOIN_DIRECTLY_JOINED, $data);
						$joined = true;
						$ses->recalculateNametag();
						break 2;
					default:
						$ses->send(Phrases::CMD_TEAM_JOIN_REQUESTED, $data);
						break 2;
				}
			}
		}
		if(isset($joined)){
			$type = ChatType::get($main, ChatType::TEAM_CHAT, "Network", "%tr%" . Phrases::CMD_TEAM_JOINED, Settings::CLASS_ALL, [
				"tid" => $this->tid,
				"teamName" => $this->teamName,
				"ign" => "Network",
				"data" => $data
			]);
			$type->push();
		}
	}
}
