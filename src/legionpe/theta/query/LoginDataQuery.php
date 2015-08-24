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

namespace legionpe\theta\query;

use legionpe\theta\BasePlugin;
use legionpe\theta\config\Settings;
use legionpe\theta\Friend;
use legionpe\theta\shops\Purchase;
use legionpe\theta\utils\MUtils;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class LoginDataQuery extends AsyncQuery{
	public $sesId;
	public $name;
	public $totalWarnPts;
	private $class;
	public function __construct(BasePlugin $plugin, $sesId, $name, $ip, $clientId){
		$this->class = Settings::$LOCALIZE_CLASS;
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
		return "SELECT uid,name,nicks,lastip,status,lastses,authuuid,coins,hash,oldhash,pwprefix,pwlen,registration,laston,ontime,config,(SELECT value FROM labels WHERE lid=users.lid)AS lbl,(SELECT approved FROM labels WHERE lid=users.lid)AS lblappr,lastgrind,rank,warnpts,lastwarn,tid,(SELECT name FROM teams WHERE tid=users.tid)as teamname,teamrank,teamjoin,ignorelist,email,emailkey,emailauth FROM users WHERE name=$this->name";
	}
	protected function onAssocFetched(\mysqli $mysql, array &$row){
		$uid = $row["uid"];
		/* group_concat must be done somewhere else because it ALWAYS returns a row. */
		$r = $mysql->query("SELECT (SELECT group_concat(ip SEPARATOR ',') FROM iphist WHERE uid=$uid) as iphist, (SELECT group_concat(lang ORDER BY priority SEPARATOR ',') FROM langs WHERE uid=$uid) AS langs, (SELECT group_concat(CONCAT(channel, ':', sublv) SEPARATOR ',') FROM channels WHERE uid=$uid) as channels, (SELECT group_concat(CONCAT(IF(smalluid=$uid,largeuid, smalluid), ':', type, ':', requested, ':', direction, ':', (SELECT name FROM users WHERE uid=IF(smalluid=$uid,largeuid, smalluid))) SEPARATOR ';') FROM friends WHERE smalluid=$uid OR largeuid=$uid) AS friends");
		$result = $r->fetch_assoc();
		$row["iphist"] = isset($result["iphist"]) ? $result["iphist"] : ",";
		$row["langs"] = isset($result["langs"]) ? array_filter(explode(",", $result["langs"])) : [];
		if(isset($result["channels"])){
			$chanData = explode(",", $result["channels"]);
			$channels = [];
			foreach($chanData as $chanDatum){
				list($key, $value) = explode(":", $chanDatum);
				$channels[$key] = (int)$value;
			}
		}else{
			$row["channels"] = [];
		}
		$friendsString = explode(";", $result["friends"]);
		$friends = [
			Friend::FRIEND_ENEMY => [],
			Friend::FRIEND_ACQUAINTANCE => [],
			Friend::FRIEND_GOOD_FRIEND => [],
			Friend::FRIEND_BEST_FRIEND => [],
			Friend::FRIEND_NOT_FRIEND => []
		];
		foreach($friendsString as $friend){
			list($friendUid, $type, $requested, $reqDir, $name) = explode(":", $friend);
			$friends[(int)$type][(int)$friendUid] = new Friend($uid, $friendUid, $type, $requested, $reqDir, $name);
		}
		$row["friends"] = $friends;
		$row["isnew"] = false;
		$r->close();
		if($this->fetchPurchases()){
			$r = $mysql->query("SELECT pid, id, amplitude, count, expiry FROM purchases WHERE uid=$uid AND class=$this->class");
			$purchases = [];
			while(is_array($result = $r->fetch_assoc())){
				$purchases[$result["pid"]] = new Purchase((int)$result["pid"], $uid, $this->class, (int)$result["id"], (int)$result["amplitude"], (int)$result["count"], (int)$result["expiry"]);
			}
			$r->close();
			$row["purchases"] = $purchases;
			if($this->fetchKits()){
				$r = $mysql->query("SELECT kitid, slot, name, value FROM kits_slots WHERE uid=$uid AND class=$this->class");
				/** @var mixed[][][] $kitRows */
				$kitRows = [];
				while(is_array($resultRow = $r->fetch_assoc())){
					$kitid = (int)$resultRow["kitid"];
					$resultRow["kitid"] = (int)$resultRow["kitid"];
					$resultRow["slot"] = (int)$resultRow["slot"];
					$resultRow["value"] = (int)$resultRow["value"];
					if(!isset($kitRows[$kitid])){
						$kitRows[$kitid] = [$resultRow];
					}else{
						$kitRows[$kitid][] = $resultRow;
					}
				}
				$r->close();
				$row["kitrowsarray"] = $kitRows;
			}
		}
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
			"oldhash" => self::COL_STRING,
			"pwprefix" => self::COL_STRING,
			"pwlen" => self::COL_INT,
			"registration" => self::COL_UNIXTIME,
			"laston" => self::COL_UNIXTIME,
			"ontime" => self::COL_INT,
			"config" => self::COL_INT,
			"lbl" => self::COL_STRING,
			"lblappr" => self::COL_INT,
			"lastgrind" => self::COL_UNIXTIME,
			"rank" => self::COL_INT,
			"warnpts" => self::COL_INT,
			"lastwarn" => self::COL_UNIXTIME,
			"tid" => self::COL_INT,
			"teamname" => self::COL_STRING,
			"teamrank" => self::COL_INT,
			"teamjoin" => self::COL_UNIXTIME,
			"ignorelist" => self::COL_STRING,
			"email" => self::COL_STRING,
			"emailkey" => self::COL_STRING,
			"emailauth" => self::COL_INT,
		];
	}
	public function onCompletion(Server $server){
		$main = BasePlugin::getInstance($server);
		foreach($server->getOnlinePlayers() as $p){
			if($p->getId() === $this->sesId){
				$player = $p;
				break;
			}
		}
		if(!isset($player)){
			$main->getLogger()->notice("Player of $this->sesId quitted the server before data were fetched.");
			return;
		}
		/** @var bool $success */
		/** @var string $query */
		extract($this->getResult());
		if(!$success){
			$player->kick(TextFormat::RED . "Sorry, our server has encountered an internal error when trying to retrieve your data from the database.", false);
			return;
		}
		/** @var int $resulttype */
		if($resulttype === AsyncQuery::TYPE_RAW){
			$main->getLogger()->notice("New account pending to register: {$this->name}");
			$loginData = null;
		}else{
			/** @var mixed[] $result */
			$loginData = $result;
			if(count($main->getSessions()) >= Settings::$SYSTEM_MAX_PLAYERS){
				$main->getLogger()->notice("Server slots exceeded optimum level!");
				$rank = (int)$loginData["rank"];
				if($rank & Settings::RANK_PERM_MOD){
					$main->getLogger()->notice($player->getName() . " bypassed as mod");
				}elseif($rank & Settings::RANK_IMPORTANCE_DONATOR){
					$main->getLogger()->notice($player->getName() . " bypassed as donator");
				}else{
					$main->getAltServer($ip, $port);
					if($ip !== "0.0.0.0"){
						$main->getLogger()->notice($player->getName() . " is transferred to $ip:$port");
						$main->transfer($player, $ip, $port, "This server is full.", false);
					}
				}
			}
			$conseq = Settings::getWarnPtsConseq($this->totalWarnPts, $loginData["lastwarn"]);
			if($conseq->banLength > 0){
				$player->kick(TextFormat::RED . "You are banned.\nYou have accumulated " . TextFormat::DARK_PURPLE . $this->totalWarnPts . TextFormat::RED . " warning points,\nand you still have " . TextFormat::BLUE . MUtils::time_secsToString($conseq->banLength) . TextFormat::RED . " before you are unbanned.\n" . TextFormat::AQUA . "Believe this to be a mistake? Contact us with email at " . TextFormat::DARK_PURPLE . "support@legionpvp.eu");
				return;
			}
		}
		$main->newSession($player, $loginData);
	}
	public function __debugInfo(){
		return [];
	}
	protected function fetchPurchases(){
		return false;
	}
	protected function fetchKits(){
		return false;
	}
	public function reportDebug(){
		return false;
	}
}
