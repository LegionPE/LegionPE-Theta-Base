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
use legionpe\theta\Session;

class WarningQuery extends AsyncQuery{
	const WARNING_TYPE_BAN = 0, WARNING_TYPE_MUTE = 0;

	/** @var string */
	private $name, $ip;

	private $uuid;
	/** @var int */
	private $clientId;
	/** @var int */
	private $type;
	/** @var string */
	private $msg;
	/** @var int */
	private $created, $duration, $issuer;
	public function __construct(BasePlugin $plugin, Session $issuer, Session $victim, $msg, $type, $duration){
		$this->name = $victim->getPlayer()->getName();
		$this->ip = $victim->getPlayer()->getAddress();
		$this->uuid = $victim->getPlayer()->getRawUniqueId();
		$this->clientId = $victim->getPlayer()->getClientId();
		$this->msg = $msg;
		$this->type = $type;
		$this->created = time();
		$this->duration = $duration;
		$this->issuer = $issuer->getUid();
		parent::__construct($plugin);
	}
	public function getQuery(){
		return "INSERT INTO warnings VALUES ('" . $this->name . "',
		'" . $this->ip . "',
		" . $this->esc($this->uuid, true) . ",
		" . $this->clientId . ",
		" . $this->type . ",
		" . $this->esc($this->msg) . ",
		" . $this->created . ",
		" . $this->duration . ",
		" . $this->issuer . ")";
	}
	public function getResultType(){
		return self::TYPE_RAW;
	}
}
