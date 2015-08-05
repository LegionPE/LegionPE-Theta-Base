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
use legionpe\theta\Session;

class PrivateMessageChatType extends ChatType{
	protected $recipient;
	public function getType(){
		return self::PRIVATE_MESSAGE;
	}
	public function execute(){
		$session = $this->main->getSessionByUid($this->recipient);
		if($session instanceof Session){
			$session->getPlayer()->sendMessage("[$this->src => {$session->getInGameName()}] " . Phrases::VAR_info . $this->msg);
			$this->consume();
		}
	}
}
