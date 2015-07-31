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

use legionpe\theta\config\Settings;
use legionpe\theta\lang\Phrases;
use legionpe\theta\Session;

class ChannelChatType extends ChatType{
	protected $channel;
	protected $fromClass;
	protected $ign;
	protected $level = Session::CHANNEL_SUB_NORMAL;
	protected $data;
	public function execute(){
		foreach($this->main->getSessions() as $ses){
			$msg = $this->msg;
			if($ses->isOnChannel($this->channel, $subLevel) and $this->canRead($ses, $subLevel, $msg)){
				if(substr($msg, 0, 4) !== "%tr%"){
					$ses->getPlayer()->sendMessage("#$this->channel " . Phrases::VAR_em . "$this->ign@" . Phrases::VAR_em2 . Settings::$CLASSES_NAMES[$this->fromClass] . ": " . Phrases::VAR_info . $this->msg);
				}else{
					$ses->send(substr($msg, 4), $this->data);
				}
			}
		}
	}
	public function getType(){
		return self::CHANNEL_CHAT;
	}
	private function canRead(Session $session, $subLevel, &$msg){
		if($subLevel < $this->level){
			return true;
		}
		if(
			$this->level === Session::CHANNEL_SUB_NORMAL and
			$subLevel === Session::CHANNEL_SUB_MENTION and
			($replace = preg_replace_callback(
				"%([^A-Za-z0-9_])({$session->getPlayer()->getName()})([^A-Za-z0-9_])%",
				function ($match){
					return $match[1] . Phrases::VAR_em3 . $match[2] . Phrases::VAR_info . $match[3];
				},
				$msg
			)) !== $msg
		){
			$msg = $replace;
			return true;
		}
		return false;
	}
}
