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

namespace legionpe\theta\query;

use legionpe\theta\BasePlugin;
use legionpe\theta\lang\Phrases;
use legionpe\theta\Session;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class ListFriendsQuery extends AsyncQuery{
	/** @var BasePlugin */
	private $main;
	/** @var Session */
	private $session;
	/** @var int */
	private $uid;
	public function __construct(BasePlugin $main, Session $session){
		$this->session = $session;
		$this->uid = $session->getUid();
		parent::__construct($this->main = $main);
	}
	public function getQuery(){
		return "SELECT type,(SELECT nicks FROM users WHERE uid=tmp.uid)AS nicks FROM(SELECT IF(smalluid=$this->uid,largeuid,smalluid)AS uid,type FROM friends WHERE smalluid=$this->uid OR largeuid=$this->uid)AS tmp";
	}
	public function getResultType(){
		return self::TYPE_ALL;
	}
	public function getExpectedColumns(){
		return [
			"type" => self::COL_INT,
			"nicks" => self::COL_STRING
		];
	}
	public function onCompletion(Server $server){
		$friends = [
			Session::FRIEND_LEVEL_NONE => [],
			Session::FRIEND_LEVEL_ACQUAINTANCE => [],
			Session::FRIEND_LEVEL_GOOD_FRIEND => [],
			Session::FRIEND_LEVEL_BEST_FRIEND => []
		];
		if($this->session->getPlayer()->isOnline()){
			$result = $this->getResult()["result"];
			foreach($result as $row){
				$type = $row["type"];
				$nick = array_filter(explode("|", $row["nicks"]))[0];
				$isOnline = $server->getPlayerExact($nick) instanceof Player;
				$class = $type & ~Session::FRIEND_BITMASK_REQUEST;
				$req = $type & Session::FRIEND_BITMASK_REQUEST;
				if($isOnline){
					$nick = TextFormat::GREEN . "*" . TextFormat::WHITE . $nick;
				}
				if($req === Session::FRIEND_IN){
					$nick .= TextFormat::GOLD . ">";
				}elseif($req === Session::FRIEND_OUT){
					$nick .= TextFormat::DARK_AQUA . "<";
				}
				$nick .= TextFormat::WHITE;
				$friends[$class][] = $nick;
			}
			$this->session->send(Phrases::CMD_FRIEND_LIST_KEY);
			$this->session->sendCurlyLines();
			foreach($friends as $class => $list){
				$type = $this->session->translate(Session::$FRIEND_TYPES[$class]);
				$this->session->setMaintainedPopup(TextFormat::BLUE . $type . ": " . implode(TextFormat::WHITE . ":", $list));
			}
			$this->session->sendCurlyLines();
		}
	}
}
