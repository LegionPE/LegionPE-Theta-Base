<?php

namespace legionpe\theta\query;

use legionpe\theta\BasePlugin;
use legionpe\theta\config\Settings;

class ReportStatusQuery extends AsyncQuery{
	private $players;
	public function __construct(BasePlugin $plugin, $players){
		parent::__construct($plugin);
		$this->players = $players;
	}
	public function getQuery(){
		$myid = Settings::$LOCALIZE_ID;
		return "UPDATE server_status SET last_online=unix_timestamp(),online_players=$this->players WHERE server_id=$myid;";
	}
	public function getResultType(){
		return self::TYPE_RAW;
	}
}
