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

use legionpe\theta\BasePlugin;
use legionpe\theta\query\PushChatQuery;

abstract class ChatType{
	const SERVER_BROADCAST = 0;
	const TEAM_CHAT = 1;
	const CONSOLE_MESSAGE = 2;
	const CHANNEL_CHAT = 3;
	const MUTE_CHAT = 4;
	/** @var BasePlugin */
	protected $main;
	protected $src;
	protected $msg;
	protected $class;
	protected $_classData;
	public function __construct(BasePlugin $main, $src, $msg, $class, $data){
		$this->main = $main;
		$this->src = $src;
		$this->msg = $msg;
		$this->class = $class;
		$this->_classData = $data;
		foreach($data as $key => $value){
			if(!isset($this->{$key})){
				$this->{$key} = $value;
			}
		}
	}
	public static function get(BasePlugin $main, $id, $src, $msg, $class, $data){
		switch($id){
			case self::SERVER_BROADCAST:
				return new ServerBroadcastChatType($main, $src, $msg, $class, $data);
			case self::TEAM_CHAT:
				return new TeamChatType($main, $src, $msg, $class, $data);
			case self::CONSOLE_MESSAGE:
				return new ConsoleChatType($main, $src, $msg, $class, $data);
			case self::CHANNEL_CHAT:
				return new ChannelChatType($main, $src, $msg, $class, $data);
			case self::MUTE_CHAT:
				return new MuteChatType($main, $src, $msg, $class, $data);
		}
		return null;
	}
	public function push(){
		$this->onPush();
		new PushChatQuery($this->main, $this->src, $this->msg, $this->getType(), $this->class, $this->_classData);
	}
	protected function onPush(){

	}
	public abstract function getType();
	public abstract function execute();
}
