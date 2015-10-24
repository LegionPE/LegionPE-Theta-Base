<?php

/*
 * LegionPE
 *
 * Copyright (C) 2015 PEMapModder
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PEMapModder
 */

namespace legionpe\theta\chat;

use legionpe\theta\Session;

class TeamRankChangeHormone extends Hormone{
	protected $uid;
	protected $newRank;
	public function getType(){
		return self::TEAM_RANK_CHANGE_HORMONE;
	}
	public function execute(){
		$ses = $this->main->getSessionByUid($this->uid);
		if($ses instanceof Session){
			$ses->setLoginDatum("teamrank", $this->newRank);
			$ses->recalculateNameTag();
		}
	}
}
