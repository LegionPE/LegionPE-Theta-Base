<?php

/*
 * LegionPE
 *
 * Copyright (C) 2015 PEMapModder
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PEMapModder
 */

namespace legionpe\theta\utils;

use pocketmine\entity\Entity;
use pocketmine\level\particle\Particle;
use pocketmine\network\protocol\AddEntityPacket;

class SpawnGhastParticle extends Particle{
	const GHAST_NETWORK_ID = 41;
	private $eid;
	public $yaw;
	public $pitch;
	public function encode(){
		$pk = new AddEntityPacket;
		$pk->eid = $this->getEid();
		$pk->type = self::GHAST_NETWORK_ID;
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = 0;
		$pk->speedY = 0;
		$pk->speedZ = 0;
		$pk->yaw = $this->yaw;
		$pk->pitch = $this->pitch;
		$pk->metadata = [
			Entity::DATA_FLAGS => [Entity::DATA_TYPE_BYTE, 0],
			Entity::DATA_AIR => [Entity::DATA_TYPE_SHORT, 300],
			Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, ""],
			Entity::DATA_SHOW_NAMETAG => [Entity::DATA_TYPE_BYTE, 0],
			Entity::DATA_SILENT => [Entity::DATA_TYPE_BYTE, 0],
			Entity::DATA_NO_AI => [Entity::DATA_TYPE_BYTE, 1],
		];
	}
	private function getEid(){
		if(isset($this->eid)){
			return $this->eid;
		}
		return $this->eid = Entity::$entityCount++;
	}
}
