<?php

/**
 * Theta
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
use legionpe\theta\shops\Kit;

class AddKitQuery extends AsyncQuery{
	/** @var int */
	private $uid;
	/** @var int */
	private $class;
	/** @var int */
	private $kitid;
	/** @var string */
	private $kitName;
	/**
	 * @param BasePlugin $main
	 * @param int $uid
	 * @param int $kitid
	 * @param $kitName
	 */
	public function __construct(BasePlugin $main, $uid, $kitid, $kitName){
		$this->uid = $uid;
		$this->class = Settings::$LOCALIZE_CLASS;
		$this->kitid = $kitid;
		$this->kitName = $kitName;
		parent::__construct($main);
	}
	public function getQuery(){
		$realSize = Kit::SLOT_SPECIAL_REAL_SIZE;
		$abstractSize = Kit::SLOT_SPECIAL_ABSTRACT_SIZE;
		$name = Kit::SLOT_SPECIAL_NAME;
		return "INSERT INTO kits_slots (uid, class, kitid, slot, value, name) VALUES ($this->uid, $this->class, $this->kitid, $realSize, 3, ''),($this->uid, $this->class, $this->kitid, $abstractSize, 1, ''),($this->uid, $this->class, $this->kitid, $name, 0, '$this->kitName')";
	}
	public function getResultType(){
		return self::TYPE_RAW;
	}
	protected function onPreQuery(\mysqli $db){
		$this->kitName = $db->escape_string($this->kitName);
	}
}
