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
use pocketmine\level\particle\LavaDripParticle;
use pocketmine\level\particle\PortalParticle;
use pocketmine\level\particle\RedstoneParticle;
use pocketmine\level\particle\SplashParticle;
use pocketmine\math\Vector3;

class RainbowWalkingParticle extends WalkingParticle{
	public function __construct(Session $session){
		parent::__construct($session);
		$this->setName("Rainbow");
		$this->setColors(["blue", "black", "grey", "red", "purle"]);
		$this->tid = self::TYPE_RAINBOW;
	}
	public function createParticles(){
		$player = $this->getSession()->getPlayer();
		//if($player->speed instanceof Vector3 and $player->speed->lengthSquared() > 0) return;
		$position = $player->getPosition();
		$level = $player->getLevel();
		$level->addParticle(new SplashParticle(new Vector3($position->getX() - 0.5 + mt_rand(1, 10) / 10, $position->getY() + 0.2, $position->getZ() - 0.5 + mt_rand(1, 10) / 10)));
		$level->addParticle(new LavaDripParticle(new Vector3($position->getX() - 0.5 + mt_rand(1, 10) / 10, $position->getY() + 0.2, $position->getZ() - 0.5 + mt_rand(1, 10) / 10)));
		$level->addParticle(new RedstoneParticle(new Vector3($position->getX() - 0.5 + mt_rand(1, 10) / 10, $position->getY() + 0.2, $position->getZ() - 0.5 + mt_rand(1, 10) / 10)));
		$level->addParticle(new PortalParticle(new Vector3($position->getX() - 0.5 + mt_rand(1, 10) / 10, $position->getY() + 0.2, $position->getZ() - 0.5 + mt_rand(1, 10) / 10)));
	}
}
