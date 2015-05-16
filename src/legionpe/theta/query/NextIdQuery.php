<?php

namespace legionpe\theta\query;

use legionpe\theta\BasePlugin;

class NextIdQuery extends AsyncQuery{
	const TEAM = "tid";
	const USER = "uid";
	/** @var string */
	private $name;
	public function __construct(BasePlugin $plugin, $name){
		parent::__construct($plugin);
		$this->name = $name;
	}
	public function onPreQuery(\mysqli $mysqli){
		$mysqli->query("LOCK TABLES ids WRITE");
	}
	public function getQuery(){
		return "SELECT value+1 AS id FROM ids WHERE name='$this->name'";
	}
	public function onPostQuery(\mysqli $mysqli){
		$mysqli->query("UPDATE ids SET value=value+1 WHERE name='$this->name'");
		$mysqli->query("UNLOCK TABLES");
	}
	public function getResultType(){
		return self::TYPE_ASSOC;
	}
	public function getExpectedColumns(){
		return ["id" => self::COL_INT];
	}
}
