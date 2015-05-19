<?php

namespace legionpe\theta\query;

use legionpe\theta\config\Settings;

class InitDbQuery extends AsyncQuery{
	public function getQuery(){
		$myid = Settings::$LOCALIZE_ID;
		$myip = Settings::$LOCALIZE_IP;
		$myport = Settings::$LOCALIZE_PORT;
		$myclass = Settings::$LOCALIZE_CLASS;
		$mypid = getmypid();
		return "INSERT INTO server_status (server_id, ip, port, class, last_online, mypid, online_players) VALUES ($myid, $myip, $myport, $myclass, unix_timestamp(), $mypid, 0) ON DUPLICATE KEY UPDATE ip = VALUES(ip), port = $myport, class = $myclass, last_online = unix_timestamp(), mypid = $mypid, online_players = 0";
	}
	public function getResultType(){
		return self::TYPE_RAW;
	}
}
