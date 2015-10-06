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
use pocketmine\Server;

class ReportStatusQuery extends AsyncQuery{
	private $players;
	private $class;
	/** @var string */
	private $myIp, $altIp = null;
	/** @var int */
	private $myPort, $altPort = null;
	private $newJoins;
	public function __construct(BasePlugin $plugin){
		$this->players = count($plugin->getServer()->getOnlinePlayers());
		$this->class = Settings::$LOCALIZE_CLASS;
		$this->myIp = Settings::$LOCALIZE_IP;
		$this->myPort = Settings::$LOCALIZE_PORT;
		$this->newJoins = $plugin->newJoins;
		$plugin->newJoins = 0;
		parent::__construct($plugin);
	}
	public function onPreQuery(\mysqli $mysql){
		$myId = Settings::$LOCALIZE_ID;
		$mysql->query("UPDATE server_status SET last_online=unix_timestamp(),online_players=$this->players, totaljoins=totaljoins+$this->newJoins WHERE server_id=$myId;");
	}
	public function getQuery(){
		return "SELECT SUM(online_players)AS online,SUM(max_players)AS max,COUNT(*)AS servers, (SELECT SUM(online_players)FROM server_status WHERE class=$this->class AND unix_timestamp()-last_online<5)AS class_online, (SELECT SUM(max_players)FROM server_status WHERE class=$this->class AND unix_timestamp()-last_online<5)AS class_max, (SELECT COUNT(*)FROM server_status WHERE class=$this->class AND unix_timestamp()-last_online<5)AS class_servers FROM server_status WHERE unix_timestamp()-last_online<5";
	}
	public function onPostQuery(\mysqli $mysql){
		$r = $mysql->query("SELECT ip,port FROM server_status WHERE class=$this->class AND unix_timestamp() - last_online < 5 AND (ip != '$this->myIp' OR port != $this->myPort) ORDER BY max_players-online_players DESC LIMIT 1");
		if($r === false){
			echo "Error executing query: $mysql->error" . PHP_EOL;
			return;
		}
		$row = $r->fetch_assoc();
		$r->close();
		if(is_array($row)){
			$this->altIp = $row["ip"];
			$this->altPort = $row["port"];
		}
	}
	public function getResultType(){
		return self::TYPE_ASSOC;
	}
	public function getExpectedColumns(){
		return [
			"online" => self::COL_INT,
			"max" => self::COL_INT,
			"servers" => self::COL_INT,
			"class_online" => self::COL_INT,
			"class_max" => self::COL_INT,
			"class_servers" => self::COL_INT,
		];
	}
	public function onCompletion(Server $server){
		$r = $this->getResult();
		if(!isset($r["result"])){
			return;
		}
		$result = $r["result"];
		$main = BasePlugin::getInstance($server);
		$main->setPlayerCount($result["online"], $result["max"], $result["class_online"], $result["class_max"]);
		$main->setServersCount($result["servers"], $result["class_servers"]);
		if($this->altIp !== null){
			$main->setAltServer($this->altIp, $this->altPort);
		}
	}
	public function __debugInfo(){
		return [];
	}
	protected function reportDebug(){
		return false;
	}
}
