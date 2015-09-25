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
use legionpe\theta\config\Settings;
use legionpe\theta\lang\Phrases;
use pocketmine\Server;

class CreateTeamQuery extends NextIdQuery{
	/** @var int */
	private $founderUid;
	/** @var string */
	private $teamName;
	public function __construct(BasePlugin $main, $founderUid, $name){
		$this->founderUid = $founderUid;
		$this->teamName = $name;
		parent::__construct($main, NextIdQuery::TEAM);
	}
	public function onPreQuery(\mysqli $db){
		$r = $db->query("SELECT COUNT(*) AS cnt FROM teams WHERE name={$this->esc($this->teamName)}");
		$cnt = (int) $r->fetch_assoc()["cnt"];
		$r->close();
		if($cnt !== 0){
			throw new \Exception(Phrases::CMD_TEAM_CREATE_ALREADY_EXISTS);
		}
		parent::onPreQuery($db);
	}
	public function onAssocFetched(\mysqli $db, array &$row){
		$id = $row["id"];
		$db->query("INSERT INTO teams (tid, name, config, req, rules, descr, points, founder) VALUES ($id, {$this->esc($this->teamName)}, 0, '', '', '', 0.0, $this->founderUid)");
	}
	public function onCompletion(Server $server){
		$main = BasePlugin::getInstance($server);
		foreach($main->getSessions() as $ses){
			if($ses->getUid() === $this->founderUid){
				$result = $this->getResult();
				if($result["success"]){
					$ses->send(Phrases::CMD_TEAM_CREATE_SUCCESS);
					$ses->setLoginDatum("tid", $this->getId());
					$ses->setLoginDatum("teamname", $this->teamName);
					$ses->setLoginDatum("teamrank", Settings::TEAM_RANK_LEADER);
					$ses->setLoginDatum("teamjointime", time());
					$ses->setLoginDatum("teampts", 0);
					$ses->recalculateNameTag();
				}else{
					$ses->send($result["error"]);
				}
				break;
			}
		}
	}
}
