<?php

/**
 * LegionPE
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
use legionpe\theta\config\Settings;
use legionpe\theta\utils\MUtils;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class LoginDataQuery extends AsyncQuery{
	public $sesId;
	public $name;
	public $totalWarnPts;
	public function __construct(BasePlugin $plugin, $sesId, $name, $ip, $clientId){
		$this->sesId = $sesId;
		$this->name = $this->esc($name);
		$this->ip = $this->esc($ip);
		$this->clientId = $clientId;
		parent::__construct($plugin);
	}
	public function onPreQuery(\mysqli $mysql){
		$r = $mysql->query("SELECT SUM(pts)AS sum FROM warnings_logs WHERE uid=(SELECT uid FROM users WHERE name=$this->name)or(SELECT COUNT(*)FROM iphist WHERE ip=$this->ip AND uid=warnings_logs.uid)>0 or (clientid=$this->clientId and clientid!=0)");
		$this->totalWarnPts = $r->fetch_assoc()["sum"];
		$r->close();
	}
	public function getQuery(){
		// warning: keep the first 7 characters ALWAYS "SELECT "
		return "SELECT * FROM users WHERE name=$this->name";
	}
	protected function onAssocFetched(\mysqli $mysql, array &$row){
		$uid = $row["uid"];
		$r = $mysql->query("SELECT (SELECT group_concat(ip SEPARATOR ',') FROM iphist WHERE uid=$uid) as iphist, (SELECT group_concat(lang ORDER BY priority SEPARATOR ',') FROM langs WHERE uid=$uid) AS langs;");
		$result = $r->fetch_assoc();
		$row["iphist"] = $result["iphist"];
		$row["langs"] = array_filter(explode(",", $result["langs"]));
		$row["email"] = BasePlugin::EMAIL_UNVERIFIED; // TODO fetch
		$row["isnew"] = false;
		$r->close();
	}
	public function getResultType(){
		return self::TYPE_ASSOC;
	}
	public function getExpectedColumns(){
		return [
			"uid" => self::COL_INT,
			"name" => self::COL_STRING,
			"nicks" => self::COL_STRING,
			"lastip" => self::COL_STRING,
			"status" => self::COL_INT,
			"lastses" => self::COL_INT,
			"authuuid" => self::COL_STRING,
			"coins" => self::COL_FLOAT,
			"hash" => self::COL_STRING,
			"pwprefix" => self::COL_STRING,
			"pwlen" => self::COL_INT,
			"registration" => self::COL_UNIXTIME,
			"laston" => self::COL_UNIXTIME,
			"ontime" => self::COL_INT,
			"config" => self::COL_INT,
			"lastgrind" => self::COL_UNIXTIME,
			"rank" => self::COL_INT,
			"warnpts" => self::COL_INT,
			"lastwarn" => self::COL_UNIXTIME,
			"tid" => self::COL_INT,
			"teamrank" => self::COL_INT,
			"teamjoin" => self::COL_UNIXTIME,
			"ignorelist" => self::COL_STRING,
		];
	}
	public function onCompletion(Server $server){
		$main = BasePlugin::getInstance($server);
		foreach($main->getServer()->getOnlinePlayers() as $player){
			if($player->getId() === $this->sesId){
				break;
			}
		}
		if(!isset($player)){
			return;
		}
		/** @var bool $success */
		/** @var string $query */
		extract($this->getResult());
		if(!$success){
			$player->close(TextFormat::RED . "Sorry, our server has encountered an internal error when trying to retrieve your data from the database.");
			return;
		}
		/** @var int $resulttype */
		if($resulttype === AsyncQuery::TYPE_RAW){
			$main->getLogger()->notice("New account pending to register: {$this->name}");
			$loginData = null;
		}else{
			/** @var mixed[] $result */
			$loginData = $result;
			$conseq = Settings::getWarnPtsConseq($this->totalWarnPts, $loginData["lastwarn"]);
			if($conseq->banLength > 0){
				$player->kick(TextFormat::RED . "You are banned.\nYou have accumulated " . TextFormat::DARK_PURPLE . $this->totalWarnPts . TextFormat::RED . " warning points,\nand you still have " . TextFormat::BLUE . MUtils::time_secsToString($conseq->banLength) . TextFormat::RED . " before you are unbanned.\n" . TextFormat::AQUA . "Believe this to be a mistake? Contact us with email at " . TextFormat::DARK_PURPLE . "support@legionpvp.eu");
			}
		}
		$main->newSession($player, $loginData);
	}
	public function __debugInfo(){
		return [];
	}
}
