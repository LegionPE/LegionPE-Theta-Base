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
use legionpe\theta\query\RawAsyncQuery;

abstract class ChatType{
	const SERVER_BROADCAST = 0;
	const TEAM_CHAT = 1;
	const CONSOLE_MESSAGE = 2;
	const CHANNEL_CHAT = 3;
	const MUTE_CHAT = 4;
	/** @deprecated */
	const PRIVATE_MESSAGE = 5;
	const TEAM_JOIN_PROPAGANDA = 6;
	const RELOAD_FRIENDS_PROPAGANDA = 7;
	const CLASS_CHAT = 8;
	/** @var BasePlugin */
	protected $main;
	protected $src;
	protected $msg;
	protected $class;
	protected $rowId;
	protected $_classData;
	protected function __construct(BasePlugin $main, $src, $msg, $class, $data, $rowId = null){
		$this->main = $main;
		$this->src = $src;
		$this->msg = $msg;
		$this->class = $class;
		$this->rowId = $rowId;
		$this->_classData = $data;
		foreach($data as $key => $value){
			if(!isset($this->{$key})){
				$this->{$key} = $value;
			}
		}
	}
	public static function get(BasePlugin $main, $id, $src, $msg, $class, $data, $rowId = null){
		switch($id){
			case self::SERVER_BROADCAST:
				return new ServerBroadcastChatType($main, $src, $msg, $class, $data, $rowId);
			case self::TEAM_CHAT:
				return new TeamChatType($main, $src, $msg, $class, $data, $rowId);
			case self::CONSOLE_MESSAGE:
				return new ConsoleChatType($main, $src, $msg, $class, $data, $rowId);
			case self::CHANNEL_CHAT:
				return new ChannelChatType($main, $src, $msg, $class, $data, $rowId);
			case self::MUTE_CHAT:
				return new MuteChatType($main, $src, $msg, $class, $data, $rowId);
			/** @noinspection PhpDeprecationInspection */
			case self::PRIVATE_MESSAGE:
				return new PrivateMessageChatType($main, $src, $msg, $class, $data, $rowId);
			case self::TEAM_JOIN_PROPAGANDA:
				return new TeamJoinPropaganda($main, $src, $msg, $class, $data, $rowId);
			case self::RELOAD_FRIENDS_PROPAGANDA:
				return new ReloadFriendsPropaganda($main, $src, $msg, $class, $data, $rowId);
			case self::CLASS_CHAT:
				return new ClassChatType($main, $src, $msg, $class, $data, $rowId);
		}
		return null;
	}
	public function push(){
		$this->onPush();
		new PushChatQuery($this->main, $this->src, $this->msg, $this->getType(), $this->class, $this->_classData);
	}
	public function consume(){
		if(is_int($this->rowId)){
			new RawAsyncQuery($this->main, "DELETE FROM chat WHERE id=$this->rowId");
			return true;
		}
		return false;
	}
	protected function onPush(){

	}
	public abstract function getType();
	public abstract function execute();
}
