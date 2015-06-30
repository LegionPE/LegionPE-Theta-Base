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

use legionpe\theta\BasePlugin;
use legionpe\theta\query\PushChatQuery;

abstract class ChatType{
	const SERVER_BROADCAST = 0;
	const TEAM_CHAT = 1;
	const CONSOLE_MESSAGE = 2;
	const CHANNEL_CHAT = 3;
	public static function get(BasePlugin $main, $id, $src, $msg, $class, $data){
		switch($id){
			case self::SERVER_BROADCAST:
				return new ServerBroadcastChatType($main, $src, $msg, $class, $data);
			case self::TEAM_CHAT:
				return new TeamChatType($main, $src, $msg, $class, $data);
			case self::CONSOLE_MESSAGE:
				return new ConsoleChatType($main, $src, $msg, $class, $data);
		}
		return null;
	}

	/** @var BasePlugin */
	protected $main;
	protected $src;
	protected $msg;
	protected $class;
	protected $data;
	public function __construct(BasePlugin $main, $src, $msg, $class, $data){
		$this->main = $main;
		$this->data = $data;
		$this->src = $src;
		$this->msg = $msg;
		$this->class = $class;
		foreach($data as $key => $value){
			if(!isset($this->{$key})){
				$this->{$key} = $value;
			}
		}
	}
	public function push(){
		new PushChatQuery($this->main, $this->src, $this->msg, $this->getType(), $this->class, $this->data);
	}
	public abstract function execute();
	public abstract function getType();
}
