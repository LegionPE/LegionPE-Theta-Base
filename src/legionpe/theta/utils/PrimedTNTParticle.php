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

use pocketmine\entity\Entity;
use pocketmine\entity\PrimedTNT;
use pocketmine\level\Level;
use pocketmine\level\particle\Particle;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\ExplodePacket;
use pocketmine\network\protocol\RemoveEntityPacket;

class PrimedTNTParticle extends Particle{
	private $name;
	private $delay;
	public $exploded = false;
	private $eid;
	public function __construct($x, $y, $z, Level $level, $name, $delay){
		parent::__construct($x, $y, $z);
		$this->name = $name;
		$this->delay = $delay;
		$this->eid = Entity::$entityCount;
	}
	public function encode(){
		if($this->exploded){
			$rm = new RemoveEntityPacket;
			$rm->eid = $this->eid;
			$ex = new ExplodePacket;
			$ex->x = $this->x;
			$ex->y = $this->x;
			$ex->z = $this->y;
			$ex->radius = 0;
			$ex->records = [];
			return [$rm, $ex];
		}else{
			$spawn = new AddEntityPacket;
			$spawn->type = PrimedTNT::NETWORK_ID;
			$spawn->eid = $this->eid;
			$spawn->x = $this->x;
			$spawn->y = $this->y;
			$spawn->z = $this->z;
			$spawn->speedX = 0;
			$spawn->speedY = 0;
			$spawn->speedZ = 0;
			$spawn->metadata = [
				Entity::DATA_FLAGS => [Entity::DATA_TYPE_BYTE, 0],
				Entity::DATA_AIR => [Entity::DATA_TYPE_SHORT, 300],
				Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $this->name],
				Entity::DATA_SHOW_NAMETAG => [Entity::DATA_TYPE_BYTE, 1],
				Entity::DATA_SILENT => [Entity::DATA_TYPE_BYTE, 0],
				Entity::DATA_NO_AI => [Entity::DATA_TYPE_BYTE, 1],
			];
			return $spawn;
		}
	}
}
