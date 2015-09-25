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

namespace legionpe\theta\chat;

use legionpe\theta\lang\Phrases;

class TeamKickHormone extends Hormone{
	private $uid;
	public function getType(){
		return self::TEAM_KICK_PROPAGANDA;
	}
	public function execute(){
		foreach($this->main->getSessions() as $ses){
			if($ses->getUid() === $this->uid){
				$ses->setLoginDatum("tid", -1);
				$ses->setLoginDatum("teamname", null);
				$ses->setLoginDatum("teamrank", 0);
				$ses->setLoginDatum("teamjoin", 0);
				$ses->setLoginDatum("teampts", 0);
				$ses->send(Phrases::CMD_TEAM_KICKED, ["src" => $this->src, "msg" => $this->msg]);
			}
		}
	}
}
