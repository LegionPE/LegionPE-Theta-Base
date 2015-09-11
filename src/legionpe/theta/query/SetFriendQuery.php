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

namespace legionpe\theta\query;

use legionpe\theta\BasePlugin;
use legionpe\theta\chat\Hormone;
use legionpe\theta\config\Settings;
use legionpe\theta\Friend;
use legionpe\theta\lang\Phrases;
use legionpe\theta\Session;
use pocketmine\Server;

class SetFriendQuery extends NameToUidQuery{
	/** @var int */
	private $senderUid;
	/** @var int */
	private $type;
	public static $RESPONSES = [
		Friend::RET_REQUEST_ALREADY_SENT => Phrases::CMD_FRIEND_REQUEST_ALREADY_SENT,
		Friend::RET_REQUEST_ACCEPTED => Phrases::CMD_FRIEND_REQUEST_ACCEPTED,
		Friend::RET_SENT_REQUEST => Phrases::CMD_FRIEND_SENT_REQUEST,
		Friend::RET_REQUEST_ACCEPTED_AND_RAISE_SENT => Phrases::CMD_FRIEND_REQUEST_ACCEPTED_AND_RAISE_SENT,
		Friend::RET_REDUCED => Phrases::CMD_FRIEND_REDUCED,
		Friend::RET_IS_CURRENT_STATE => Phrases::CMD_FRIEND_IS_CURRENT_STATE,
		Friend::RET_RAISED_REQUEST => Phrases::CMD_FRIEND_REQUEST_RAISED,
		Friend::RET_REQUEST_REDUCED => Phrases::CMD_FRIEND_REQUEST_REDUCED,
		Friend::RET_REQUEST_CANCELLED => Phrases::CMD_FRIEND_REQUEST_CANCELLED,
		Friend::RET_REQUEST_CANCELLED_AND_REDUCED => Phrases::CMD_FRIEND_REQUEST_CANCELLED_AND_REDUCED,
		Friend::RET_REQUEST_REJECTED => Phrases::CMD_FRIEND_REQUEST_REJECTED,
		Friend::RET_REQUEST_REJECTED_AND_LOWER_SENT => Phrases::CMD_FRIEND_REQUEST_REJECTED_AND_LOWER_SENT,
		Friend::RET_REQUEST_REJECTED_AND_REDUCED => Phrases::CMD_FRIEND_REQUEST_REJECTED_AND_REDUCED,
	];
	public static $TYPES = [
		Friend::FRIEND_ENEMY => "enemy",
		Friend::FRIEND_NOT_FRIEND => "normal",
		Friend::FRIEND_ACQUAINTANCE => "acquaintance",
		Friend::FRIEND_GOOD_FRIEND => "good friend",
		Friend::FRIEND_BEST_FRIEND => "best friend",
	];
	public function __construct(BasePlugin $main, $name, $senderUid, $type){
		$this->senderUid = $senderUid;
		$this->type = $type;
		parent::__construct($main, $name);
	}
	public function onCompletion(Server $server){
		$main = BasePlugin::getInstance($server);
		$ses = $main->getSessionByUid($this->senderUid);
		if(!($ses instanceof Session)){
			return;
		}
		$result = $this->getResult();
		if($result["resulttype"] !== self::TYPE_ASSOC){
			$ses->send(Phrases::CMD_ERR_NOT_FOUND, ["name" => $this->name]);
			return;
		}
		$targetUid = $result["result"]["uid"];
		$result = $ses->setFriendAttempt($targetUid, $this->type, $prop);
		if($prop){
			$type = Hormone::get($main, Hormone::RELOAD_FRIENDS_PROPAGANDA, $ses->getPlayer()->getName(), "$result", Settings::CLASS_ALL, [
				"uid" => $targetUid
			]);
			$type->push();
			$type = Hormone::get($main, Hormone::RELOAD_FRIENDS_PROPAGANDA, $this->name, "$result", Settings::CLASS_ALL, [
				"uid" => $this->senderUid
			]);
			$type->push();
		}
		$ses->send(self::$RESPONSES[$result], [
			"from" => $ses->getInGameName(),
			"to" => $this->name,
			"target" => $this->name,
			"req" => self::$TYPES[$this->type],
			"cur" => self::$TYPES[$ses->getFriend($targetUid)->type]
		]);
	}
}
