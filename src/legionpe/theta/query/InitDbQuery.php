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

use legionpe\theta\config\Settings;

class InitDbQuery extends AsyncQuery{
	public function getQuery(){
		$myId = Settings::$LOCALIZE_ID;
		$myIp = $this->esc(Settings::$LOCALIZE_IP);
		$myPort = Settings::$LOCALIZE_PORT;
		$myClass = Settings::$LOCALIZE_CLASS | (Settings::$SYSTEM_IS_TEST ? 0x80 : 0);
		$myPid = $this->esc(getmypid());
		$maxPlayers = Settings::$SYSTEM_MAX_PLAYERS;
		return "INSERT INTO server_status (server_id, ip, port, class, last_online, mypid, online_players, max_players, totaljoins, joinssince) VALUES ($myId, $myIp, $myPort, $myClass, unix_timestamp(), $myPid, 0, $maxPlayers, 0, unix_timestamp()) ON DUPLICATE KEY UPDATE ip = VALUES(ip), port = $myPort, class = $myClass, last_online = unix_timestamp(), mypid = $myPid, online_players = 0, max_players = $maxPlayers, totaljoins = 0, joinssince = unix_timestamp()";
	}
	public function getResultType(){
		return self::TYPE_RAW;
	}
	public function __debugInfo(){
		return [];
	}
}
