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

namespace legionpe\theta\miscellaneous\walkingparticle;

use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\level\particle\LavaDripParticle;
use pocketmine\math\Vector3;

class LavaDrippingWalkingParticle extends WalkingParticle{
	public function __construct(){
		$this->setName("Lava dripping");
		$this->setColors(["orange", "red"]);
	}
	public function onMove(PlayerMoveEvent $event){
		$player = $event->getPlayer();
		$position = $event->getPlayer()->getPosition();
		for($i=0;$i<2;$i++){
			$player->getLevel()->addParticle(new LavaDripParticle(new Vector3($position->getX() - 0.5 + mt_rand(1, 10) / 10, $position->getY() + 0.2, $position->getZ() - 0.5 + mt_rand(1, 10) / 10)));
		}
	}
}
