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
use pocketmine\Server;

class ReportStatusQuery extends AsyncQuery{
	private $players;
	private $class;
	/** @var string */
	private $altIp = null;
	/** @var int */
	private $altPort = null;
	public function __construct(BasePlugin $plugin){
		parent::__construct($plugin);
		$this->players = count($plugin->getServer()->getOnlinePlayers());
		$this->class = Settings::$LOCALIZE_CLASS;
	}
	public function onPreQuery(\mysqli $mysql){
		$myid = Settings::$LOCALIZE_ID;
		$mysql->query("UPDATE server_status SET last_online=unix_timestamp(),online_players=$this->players WHERE server_id=$myid;");
	}
	public function getQuery(){
		return "SELECT SUM(online_players)AS online,SUM(max_players)AS max,(SELECT SUM(online_players)FROM server_status WHERE class=$this->class)AS class_online,(SELECT SUM(max_players)FROM server_status WHERE class=$this->class)AS class_max FROM server_status WHERE unix_timestamp()-last_online<5";
	}
	public function onPostQuery(\mysqli $mysql){
		$r = $mysql->query("SELECT ip,port FROM server_status WHERE class=$this->class ORDER BY max_players-online_players DESC LIMIT 1");
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
			"max" => self::COL_INT
		];
	}
	protected function reportDebug(){
		return false;
	}
	public function onCompletion(Server $server){
		$r = $this->getResult();
		if(!isset($r["result"])){
			return;
		}
		$result = $r["result"];
		$main = BasePlugin::getInstance($server);
		$main->setPlayerCount($result["online"], $result["max"], $result["class_online"], $result["class_max"]);
		if($this->altIp !== null){
			$main->setAltServer($this->altIp, $this->altPort);
		}
	}
	public function __debugInfo(){
		return [];
	}
}
