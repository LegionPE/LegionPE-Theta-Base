<?php

/*
 * LegionPE
 *
 * Copyright (C) 2015 LegendsOfMCPE and contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author LegendsOfMCPE
 */

namespace legionpe\theta\utils;

use pocketmine\network\protocol\DataPacket;

class TransferPacket extends DataPacket{
	const NETWORK_ID = 0x1b;
	public $address;
	public $port = 19132;
	public function pid(){
		return 0x1b;
	}
	protected function putAddress($address, $port, $version = 4){
		$this->putByte($version);
		if($version === 4){
			foreach(explode(".", $address) as $b){
				$this->putByte(~(int) $b);
			}
			$this->putShort($port);
		}else{
			//IPv6
		}
	}
	public function decode(){
	}
	public function encode(){
		$this->reset();
		$this->putAddress($this->address, $this->port);
	}
}
