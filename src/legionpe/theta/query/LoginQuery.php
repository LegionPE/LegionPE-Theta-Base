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

class LoginQuery extends AsyncQuery{
	public $name;
	public $totalWarnPts;
	public function __construct(BasePlugin $plugin, $name, $ip, $clientId){
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
		$r = $mysql->query("SELECT group_concat(ip SEPARATOR ',') AS iphist FROM iphist WHERE uid=$uid");
		$row["iphist"] = $r->fetch_assoc()["iphist"];
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
			"iphist" => self::COL_STRING
		];
	}
	public function __debugInfo(){
		return [];
	}
}
