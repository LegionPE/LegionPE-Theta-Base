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
use legionpe\theta\Session;
use pocketmine\Server;

class InviteTeamQuery extends AsyncQuery{
	private $issuerName;
	private $issuerUid;
	private $tid;
	private $teamName;
	private $queriedTargetName;
	private $targetUid;
	private $targetName;
	private $warnInTeam = false;
	private $type;
	public function __construct(BasePlugin $main, Session $issuer, $name){
		$this->issuerName = $issuer->getPlayer()->getName();
		$this->issuerUid = $issuer->getUid();
		$this->tid = $issuer->getTeamId();
		$this->teamName = $issuer->getTeamName();
		$this->queriedTargetName = $name;
		parent::__construct($main);
	}
	public function onPreQuery(\mysqli $db){
		$result = $db->query("SELECT uid, name, tid, (SELECT type FROM tjrequests WHERE team=$this->tid AND user=users.uid) as type FROM users WHERE name='{$this->esc($this->queriedTargetName)}'");
		$row = $result->fetch_assoc();
		$result->close();
		if(is_array($row)){
			$this->targetUid = (int)$row["uid"];
			$this->targetName = $row["name"];
			$utid = (int)$row["tid"];
			if($utid === $this->tid){
				throw new \RuntimeException(Phrases::CMD_TEAM_INVITE_SAME_TEAM);
			}
			if($utid !== -1){
				$this->warnInTeam = true;
			}
		}else{
			throw new \RuntimeException(Phrases::CMD_TEAM_INVITE_NO_PLAYER);
		}
		$this->type = (int)$row["type"];
		if($this->type === JoinTeamQuery::REQUEST_FROM_TEAM){
			throw new \RuntimeException(Phrases::CMD_TEAM_INVITE_ALREADY_SENT);
		}
		if($this->warnInTeam and $this->type === JoinTeamQuery::REQUEST_FROM_USER){
			$this->warnInTeam = false;
			throw new \RuntimeException(Phrases::CMD_TEAM_INVITE_ACCEPT_TARGET_IN_TEAM);
		}
	}
	public function getQuery(){
		$toUser = JoinTeamQuery::REQUEST_FROM_TEAM;
		return $this->type === JoinTeamQuery::REQUEST_FROM_USER ? "DELETE FROM tjrequests WHERE team=$this->tid AND user=$this->targetUid" : "INSERT INTO tjrequests (team, user, type) VALUES ($this->tid, $this->targetUid, $toUser)";
	}
	public function getResultType(){
		return self::TYPE_RAW;
	}
	public function onCompletion(Server $server){
		$result = $this->getResult();
		$main = BasePlugin::getInstance($server);
		$sender = $main->getSessionByUid($this->issuerUid);
		if($result["success"] === false){
			$sender->send($result["error"]);
			return;
		}
		if($this->warnInTeam){
			$sender->send(Phrases::CMD_TEAM_INVITE_TARGET_IN_TEAM);
		}
		if($this->type === JoinTeamQuery::REQUEST_FROM_USER){
			$sender->send(Phrases::CMD_TEAM_INVITE_ACCEPTED_SENDER, ["name" => $this->targetName]);
		}else{
			$sender->send(Phrases::CMD_TEAM_INVITE_SENT, ["name" => $this->targetName]);
		}
		$type = ChatType::get($main, ChatType::TEAM_JOIN_PROPAGANDA, $this->issuerName, "", Settings::CLASS_ALL, [
			"uid" => $this->targetUid,
			"tid" => $this->tid,
			"teamName" => $this->teamName
		]);
		$type->push();
		$type = ChatType::get($main, ChatType::TEAM_CHAT, "Network", "%tr%" . Phrases::CMD_TEAM_JOINED, Settings::CLASS_ALL, [
			"tid" => $this->tid,
			"teamName" => $this->teamName,
			"ign" => "Network",
			"data" => ["teamname" => $this->teamName, "name" => $this->targetName]
		]);
		$type->push();
	}
}
