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
use legionpe\theta\query\ReloadFriendsQuery;
use legionpe\theta\Session;

class ReloadFriendsPropaganda extends ChatType{
	protected $uid;
	public function getType(){
		return self::RELOAD_FRIENDS_PROPAGANDA;
	}
	public function execute(){
		$ses = $this->main->getSessionByUid($this->uid);
		if($ses instanceof Session){
			new ReloadFriendsQuery($this->main, $this->uid);
			$ses->send(Phrases::CMD_FRIEND_PROPAGANDA, ["src" => $this->src]);
		}
	}
}
