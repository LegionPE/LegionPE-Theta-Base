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

class TeamDisbandHormone extends Hormone{
	protected $tid;
	public function getType(){
		return self::TEAM_DISBAND_PROPAGANDA;
	}
	public function execute(){
		foreach($this->main->getSessions() as $ses){
			if($ses->getTeamId() === $this->tid){
				$ses->send(Phrases::CMD_TEAM_DISBANDED);
				$ses->setLoginDatum("tid", -1);
				$ses->setLoginDatum("teamname", "");
				$ses->setLoginDatum("teamrank", 0);
				$ses->setLoginDatum("teamjoin", 0);
				$ses->setLoginDatum("teampts", 0);
				$ses->recalculateNameTag();
			}
		}
	}
}
