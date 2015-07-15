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

namespace legionpe\theta\chat;

use legionpe\theta\lang\Phrases;

class TeamChatType extends ChatType{
	protected $tid;
	protected $teamName;
	protected $ign;
	public function execute(){
		foreach($this->main->getSessions() as $ses){
			if($ses->getTeamId() === $this->tid){
				$ses->send(Phrases::CHAT_FORMAT_TEAM, [
					"source" => $this->ign,
					"msg" => $this->msg
				]);
			}
		}
		$this->main->getLogger()->info("{Team $this->teamName}" . $this->msg);
	}
	public function getType(){
		return $this->getType();
	}
}
