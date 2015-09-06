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
	protected $data;
	public function execute(){
		if(!isset($this->data)){
			$this->data = [];
		}
		foreach($this->main->getSessions() as $ses){
			if($ses->getTeamId() === $this->tid){
				$msg = (substr($this->msg, 0, 4) === "%tr%") ? $ses->translate(substr($this->msg, 4), $this->data) : $this->msg;
				$ses->send(Phrases::CHAT_FORMAT_TEAM, [
					"source" => $this->ign,
					"msg" => $msg // shall we let it translate in Session->translate()?
				]);
			}
		}
		$this->main->getLogger()->info("{Team $this->teamName} <$this->ign>" . $this->msg);
	}
	public function getType(){
		return self::TEAM_CHAT;
	}
}
