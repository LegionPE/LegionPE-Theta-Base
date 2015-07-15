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

class NextIdQuery extends AsyncQuery{
	const TEAM = "tid";
	const USER = "uid";
	const WARNING = "wid";
	const PURCHASE = "pid";
	const LABEL = "lid";
	/** @var string */
	private $name;
	private $resultId;
	public function __construct(BasePlugin $plugin, $name){
		$this->name = $name;
		parent::__construct($plugin);
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
	/**
	 * @return int|null
	 */
	public function getId(){
		if(isset($this->resultId)){
			return $this->resultId;
		}
		$result = $this->getResult();
		return $this->resultId = ($result["type"] === self::TYPE_ASSOC ? $result["result"]["id"] : null);
	}
	public function __debugInfo(){
		return [];
	}
}
