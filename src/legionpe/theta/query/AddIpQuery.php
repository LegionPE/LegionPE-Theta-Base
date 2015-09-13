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

class AddIpQuery extends AsyncQuery{
	/** @var string */
	private $newIp;
	/** @var int */
	private $uid;
	/**
	 * @param BasePlugin $plugin
	 * @param string $newIp
	 * @param int $uid
	 */
	public function __construct(BasePlugin $plugin, $newIp, $uid){
		$this->newIp = $newIp;
		$this->uid = $uid;
		parent::__construct($plugin);
	}
	public function getQuery(){
		return "INSERT INTO iphist (ip, uid) VALUES ({$this->esc($this->newIp)}, $this->uid) ON DUPLICATE KEY UPDATE uid=uid";
	}
	public function getResultType(){
		return self::TYPE_RAW;
	}
}
