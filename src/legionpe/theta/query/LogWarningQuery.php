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
	public function __construct(BasePlugin $plugin, $wid, $uid, $ip, $clientId, $issuerName, $points, $msg, $creation, $expiry){
		$this->wid = $wid;
		$this->uid = $uid;
		$this->ip = $ip;
		$this->clientId = $clientId;
		$this->issuerName = $issuerName;
		$this->points = $points;
		$this->msg = $msg;
		$this->creation = $creation;
		$this->expiry = $expiry;
		parent::__construct($plugin);
	}
	public function getQuery(){
		return "INSERT INTO warnings_logs(wid,uid,ip,clientid,issuer,pts,msg,creation,expiry,agent)VALUES($this->wid,$this->uid,{$this->esc($this->ip)},$this->clientId,{$this->esc($this->issuerName)},$this->points,{$this->esc($this->msg)},$this->creation,$this->expiry,'eu.legionpvp.theta.mysqli')";
	}
	public function getResultType(){
		return self::TYPE_RAW;
	}
}
