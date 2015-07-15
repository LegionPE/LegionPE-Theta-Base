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
use pocketmine\utils\TextFormat;

class ChannelChatType extends ChatType{
	protected $channel;
	protected $fromClass;
	protected $ign;
	public function execute(){
		foreach($this->main->getSessions() as $ses){
			if($ses->isOnChannel($this->channel)){
				$ses->getPlayer()->sendMessage(TextFormat::YELLOW . "#$this->channel " . "$this->ign@" . TextFormat::BLUE . Settings::$CLASSES_NAMES[$this->fromClass] . ": $this->msg");
			}
		}
	}
	public function getType(){
		return self::CHANNEL_CHAT;
	}
}
