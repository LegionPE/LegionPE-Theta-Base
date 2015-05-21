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
		parent::__construct($plugin);
		$this->newIp = $newIp;
		$this->uid = $uid;
	}
	public function getQuery(){
		return "INSERT INTO iphist (ip, uid) VALUES ('{$this->esc($this->newIp)}', $this->uid)";
	}
	public function getResultType(){
		return self::TYPE_RAW;
	}
}
