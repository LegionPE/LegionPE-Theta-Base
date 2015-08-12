<?php

/*
 * Theta
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

class ClassChatType extends ChatType{
	protected $ip;
	protected $port;
	protected $symbol;
	protected $local;
	public function getType(){
		return self::CLASS_CHAT;
	}
	public function execute(){
		if(!$this->local){
			foreach($this->main->getSessions() as $ses){
				if($ses->isClassChatOn()){
					$ses->sendMessage(Phrases::VAR_em . $this->src . Phrases::VAR_verbosemid . "/$this->ip:$this->port>" . Phrases::VAR_info . $this->msg);
				}
			}
		}
	}
}
