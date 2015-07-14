<?php

/**
 * Theta
 * Copyright (C) 2015 PEMapModder
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
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
