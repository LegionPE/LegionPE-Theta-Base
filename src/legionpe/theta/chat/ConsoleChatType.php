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

class ConsoleChatType extends ChatType{
	/** @var string */
	protected $ip;
	/** @var int */
	protected $port;
	public function execute(){
		$this->main->getLogger()->alert($this->src . "@$this->ip:$this->port executed /console: " . TextFormat::YELLOW . $this->msg);
		foreach($this->main->getSessions() as $ses){
			if($ses->isAdmin()){
				$ses->sendMessage($this->src . "@$this->ip:$this->port executed /console:");
				$ses->sendMessage($this->msg);
			}
		}
	}
	public function getType(){
		return self::CONSOLE_MESSAGE;
	}
}
