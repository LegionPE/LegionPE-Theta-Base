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
	/** @var int */
	private $realSize, $abstractSize;
	/**
	 * @param BasePlugin $main
	 * @param int $uid
	 * @param int $kitid
	 * @param $kitName
	 * @param $realSize
	 * @param $abstractSize
	 */
	public function __construct(BasePlugin $main, $uid, $kitid, $kitName, $realSize, $abstractSize){
		$this->uid = $uid;
		$this->class = Settings::$LOCALIZE_CLASS;
		$this->kitid = $kitid;
		$this->kitName = $kitName;
		$this->realSize = $realSize;
		$this->abstractSize = $abstractSize;
		parent::__construct($main);
	}
	public function getQuery(){
		$realSize = Kit::SLOT_SPECIAL_REAL_SIZE;
		$abstractSize = Kit::SLOT_SPECIAL_ABSTRACT_SIZE;
		$name = Kit::SLOT_SPECIAL_NAME;
		return "INSERT INTO kits_slots (uid, class, kitid, slot, value, name) VALUES ($this->uid, $this->class, $this->kitid, $realSize, $this->realSize, ''),($this->uid, $this->class, $this->kitid, $abstractSize, $this->abstractSize, ''),($this->uid, $this->class, $this->kitid, $name, 0, '$this->kitName')";
	}
	public function getResultType(){
		return self::TYPE_RAW;
	}
	protected function onPreQuery(\mysqli $db){
		$this->kitName = $db->escape_string($this->kitName);
	}
}
