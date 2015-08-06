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

namespace legionpe\theta\chat;

use legionpe\theta\config\Settings;
use legionpe\theta\lang\Phrases;
use legionpe\theta\Session;

class TeamJoinPropaganda extends ChatType{
	protected $uid;
	protected $tid;
	protected $teamName;
	public function getType(){
		return self::TEAM_JOIN_PROPAGANDA;
	}
	public function execute(){
		$ses = $this->main->getSessionByUid($this->uid);
		if(!($ses instanceof Session)){
			return;
		}
		$ses->setLoginDatum("tid", $this->tid);
		$ses->setLoginDatum("teamname", $this->teamName);
		$ses->setLoginDatum("teamrank", Settings::TEAM_RANK_JUNIOR);
		$ses->setLoginDatum("teamjointime", time());
		$ses->setLoginDatum("teampts", 0);
		$ses->send(Phrases::CMD_TEAM_INVITE_ACCEPTED_TARGET, ["teamname" => $this->teamName]);
	}
}
