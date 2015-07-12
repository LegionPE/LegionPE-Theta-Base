<?php

/**
 * LegionPE
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

class LogWarningQuery extends AsyncQuery{
	/** @var int */
	private $wid, $uid, $clientId;
	/** @var string */
	private $issuerName;
	/** @var int */
	private $points;
	/** @var string */
	private $msg;
	/** @var int */
	private $creation, $expiry;
	public function __construct(BasePlugin $plugin, $wid, $uid, $clientId, $issuerName, $points, $msg, $creation, $expiry){
		$this->wid = $wid;
		$this->uid = $uid;
		$this->clientId = $clientId;
		$this->issuerName = $this->esc($issuerName);
		$this->points = $points;
		$this->msg = $this->esc($msg);
		$this->creation = $creation;
		$this->expiry = $expiry;
		parent::__construct($plugin);
	}
	public function getQuery(){
		return "INSERT INTO warnings_logs(wid,uid,clientid,issuer,pts,msg,creation,expiry,agent)VALUES($this->wid,$this->uid,$this->clientId,$this->issuerName,$this->points,$this->msg,$this->creation,$this->expiry,'eu.legionpvp.theta.mysqli')";
	}
	public function getResultType(){
		return self::TYPE_RAW;
	}
}
