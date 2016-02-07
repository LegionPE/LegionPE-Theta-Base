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

use legionpe\theta\BasePlugin;
use legionpe\theta\Session;
use pocketmine\level\particle\InkParticle;
use pocketmine\math\Vector3;

class InkWalkingParticle extends WalkingParticle{
	public function __construct(Session $session){
		parent::__construct($session);
		$this->setName("Squid ink");
		$this->setColors(["black"]);
		$this->tid = self::TYPE_INK;
	}
	public function createParticles(){
		$player = $this->getSession()->getPlayer();
		//if($player->speed instanceof Vector3 and $player->speed->lengthSquared() > 0) return;
		$position = $player->getPosition();
		$level = $player->getLevel();
		for($i=0;$i<2;$i++){
			$level->addParticle(new InkParticle(new Vector3($position->getX() - 0.5 + mt_rand(1, 10) / 10, $position->getY() + 0.2, $position->getZ() - 0.5 + mt_rand(1, 10) / 10)));
		}
	}
}
