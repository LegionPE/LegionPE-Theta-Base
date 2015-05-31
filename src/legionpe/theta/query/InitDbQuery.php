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

use legionpe\theta\config\Settings;

class InitDbQuery extends AsyncQuery{
	public function getQuery(){
		$myid = Settings::$LOCALIZE_ID;
		$myip = $this->esc(Settings::$LOCALIZE_IP);
		$myport = Settings::$LOCALIZE_PORT;
		$myclass = Settings::$LOCALIZE_CLASS;
		$mypid = $this->esc(getmypid());
		$maxplayers = Settings::$SYSTEM_MAX_PLAYERS;
		return "INSERT INTO server_status (server_id, ip, port, class, last_online, mypid, online_players, max_players) VALUES ($myid, $myip, $myport, $myclass, unix_timestamp(), $mypid, 0, $maxplayers) ON DUPLICATE KEY UPDATE ip = VALUES(ip), port = $myport, class = $myclass, last_online = unix_timestamp(), mypid = $mypid, online_players = 0, max_players = $maxplayers";
	}
	public function getResultType(){
		return self::TYPE_RAW;
	}
	public function __debugInfo(){
		return [];
	}
}
