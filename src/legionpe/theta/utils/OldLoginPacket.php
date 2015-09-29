<?php

/*
 * LegionPE
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

namespace legionpe\theta\utils;

use pocketmine\network\protocol\DataPacket;

class OldLoginPacket extends DataPacket{
	const NETWORK_ID = 0x82;
	public $username;
	public $protocol1;
	public $protocol2;
	public $clientId;

	public $slim = false;
	public $skin = null;

	public function decode(){
		$this->username = $this->getString();
		$this->protocol1 = $this->getInt();
		$this->protocol2 = $this->getInt();
		$this->clientId = $this->getInt();
		if($this->protocol1 < 21){ //New fields!
			$this->setBuffer(null, 0); //Skip batch packet handling
			return;
		}
		$this->slim = $this->getByte() > 0;
		$this->skin = $this->getString();
	}

	public function encode(){
	}
}
