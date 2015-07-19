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
		return "SELECT uid,name,nicks,lastip,status,lastses,authuuid,coins,hash,newhash,pwprefix,pwlen,registration,laston,ontime,config,(SELECT value FROM labels WHERE lid=users.lid)AS lbl,(SELECT approved FROM labels WHERE lid=users.lid)AS lblappr,lastgrind,rank,warnpts,lastwarn,tid,(SELECT name FROM teams WHERE tid=users.tid)as teamname,teamrank,teamjoin,ignorelist,email,emailkey FROM users WHERE name=$this->name";
	}
	protected function onAssocFetched(\mysqli $mysql, array &$row){
		$uid = $row["uid"];
		/* group_concat must be done somewhere else because it ALWAYS returns a row. */
		$r = $mysql->query("SELECT (SELECT group_concat(ip SEPARATOR ',') FROM iphist WHERE uid=$uid) as iphist, (SELECT group_concat(lang ORDER BY priority SEPARATOR ',') FROM langs WHERE uid=$uid) AS langs, (SELECT group_concat(CONCAT(IF(smalluid=$uid, largeuid, smalluid), ':', type) SEPARATOR ',') FROM friends WHERE smalluid=$uid OR largeuid=$uid) AS friends, (SELECT group_concat(CONCAT(channel, ':', sublv) SEPARATOR ',') FROM channels WHERE uid=$uid);");
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
		$row["friends"] = [];
		if(isset($result["friends"])){
			foreach(array_filter(explode(",", $result["friends"])) as $friend){
				list($other, $type) = explode(":", $friend, 2);
				$row["friends"][(int)$other] = (int)$type;
			}
		}
		$row["isnew"] = false;
		$r->close();
		if($this->fetchPurchases()){
			$r = $mysql->query("SELECT pid, id, amplitude, count, expiry FROM purchases WHERE uid=$uid AND class=$this->class");
			$purchases = [];
			while(is_array($result = $r->fetch_assoc())){
				$purchases[$result["pid"]] = new Purchase($result["pid"], $uid, $this->class, $result["id"], $result["amplitude"], $result["count"], $result["expiry"]);
			}
			$r->close();
			$row["purchases"] = $purchases;
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
			"newhash" => self::COL_STRING,
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
		];
	}
	public function onCompletion(Server $server){
		$main = BasePlugin::getInstance($server);
		foreach($server->getOnlinePlayers() as $player){
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
			if(count($main->getSessions()) >= Settings::$SYSTEM_MAX_PLAYERS and isset($loginData["rank"]) and !(($loginData["rank"] & Settings::RANK_IMPORTANCE_DONATOR) or ($loginData["rank"] & Settings::RANK_PERM_MOD))){
				$main->getAltServer($ip, $port);
				$main->transfer($player, $ip, $port, "This server is full.", false);
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
}
