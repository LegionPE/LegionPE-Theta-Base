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

use pocketmine\utils\TextFormat;

class ServerBroadcastChatType extends ChatType{
	public function execute(){
		$this->main->getServer()->broadcastMessage(TextFormat::LIGHT_PURPLE . "[Network] " . $this->msg);
		$this->main->getLogger()->alert("[BROADCAST] $this->msg");
	}
	public function getType(){
		return self::SERVER_BROADCAST;
	}
}
