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
use pocketmine\Server;

class SetLabelQuery extends AsyncQuery{
	/** @var int */
	private $uid, $lid;
	public function __construct(Session $session, $lid){
		$this->uid = $session->getUid();
		$this->lid = $lid;
		parent::__construct($session->getMain());
	}
	public function getResultType(){
		return self::TYPE_RAW;
	}
	public function getQuery(){
		return "UPDATE users SET lid=$this->lid WHERE uid=$this->uid";
	}
	public function onCompletion(Server $server){
		$ses = BasePlugin::getInstance($server)->getSessionByUid($this->uid);
		if($ses instanceof Session){
			$ses->recalculateNametag();
		}
	}
}
