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

namespace legionpe\theta\command\session;

use legionpe\theta\BasePlugin;
use legionpe\theta\command\SessionCommand;
use legionpe\theta\lang\Phrases;
use legionpe\theta\Session;

class ChannelCommand extends SessionCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "channel", "Choose or view your current channel", "/ch local|team|<channel name> " . Phrases::VAR_em . "OR" . Phrases::VAR_info . " /ch quit <channel name>", ["ch", "chan"]);
	}
	protected function run(array $args, Session $sender){
		if(!isset($args[0])){
			switch($sender->getCurrentChatState()){
				case Session::CHANNEL_LOCAL:
					$sender->send(Phrases::CMD_CHANNEL_VIEW_LOCAL);
					break;
				case Session::CHANNEL_TEAM:
					$sender->send(Phrases::CMD_CHANNEL_VIEW_TEAM);
					break;
				default:
					$sender->send(Phrases::CMD_CHANNEL_VIEW_OTHER, ["chan" => $sender->getCurrentChatState()]);
			}
			return $sender->translate(Phrases::CMD_CHANNEL_VIEW_SUBSCRIBING_TO, ["channels" => implode(", ", $sender->getChannelSubscriptions())]);
		}
		$name = array_shift($args);
		if(strtolower($name) === "local"){
			$sender->setCurrentChatState(Session::CHANNEL_LOCAL);
			return $sender->translate(Phrases::CMD_CHANNEL_SET_LOCAL);
		}elseif(strtolower($name) === "team"){
			if($sender->getTeamId() === -1){
				return $sender->translate(Phrases::CMD_TEAM_ERR_NOT_IN_TEAM);
			}
			$sender->setCurrentChatState(Session::CHANNEL_TEAM);
			return $sender->translate(Phrases::CMD_CHANNEL_SET_TEAM);
		}elseif(strtolower($name) === "quit"){
			if(!isset($args[0])){
				return false;
			}
			$ch = array_shift($args);
			if(!$sender->isOnChannel($ch)){
				return $sender->translate(Phrases::CMD_CHANNEL_QUIT_NOT_ON_CHANNEL);
			}
			$sender->partChannel($ch);
			return $sender->translate(Phrases::CMD_CHANNEL_QUIT_SUCCESS);
		}else{
			if(!$sender->isOnChannel($name)){
				$sender->joinChannel($name);
				$sender->send(Phrases::CMD_CHANNEL_JOINED_SELF, ["channel" => $name]);
			}
			$sender->setCurrentChatState($name);
			return $sender->translate(Phrases::CMD_CHANNEL_SET_OTHER, ["chan" => $name]);
		}
	}
}
