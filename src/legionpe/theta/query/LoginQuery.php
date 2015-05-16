<?php

namespace legionpe\theta\query;

use legionpe\theta\BasePlugin;

class LoginQuery extends AsyncQuery{
	private $name;
	public function __construct(BasePlugin $plugin, $name){
		$this->name = $name;
		parent::__construct($plugin);
	}
	public function getQuery(){
		return "SELECT * FROM users WHERE name={$this->esc($this->name)}";
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
			"registration" => self::COL_UNIXTIME,
			"laston" => self::COL_UNIXTIME,
			"ontime" => self::COL_INT,
			"config" => self::COL_INT,
			"lastgrind" => self::COL_UNIXTIME,
			"rank" => self::COL_INT,
			"warnpts" => self::COL_INT,
			"tid" => self::COL_INT,
			"teamrank" => self::COL_INT,
			"teamjoin" => self::COL_UNIXTIME,
			"ignorelist" => self::COL_STRING
		];
	}
}
