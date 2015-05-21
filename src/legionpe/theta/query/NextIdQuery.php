<?php

/**
 * LegionPE-Theta
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

class NextIdQuery extends AsyncQuery{
	const TEAM = "tid";
	const USER = "uid";
	const WARNING = "wid";
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
