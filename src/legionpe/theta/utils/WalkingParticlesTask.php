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

namespace legionpe\theta\utils;

use legionpe\theta\BasePlugin;
use pocketmine\math\Vector3;
use pocketmine\scheduler\PluginTask;

class WalkingParticlesTask extends PluginTask{
	/** @var BasePlugin */
	private $plugin;
	public function __construct(BasePlugin $plugin){
		parent::__construct($plugin);
		$this->plugin = $plugin;
	}
	public function onRun($ticks){
		foreach($this->plugin->getSessions() as $session){
			foreach($session->walkingParticles as $walkingParticle){
				$walkingParticle->createParticles();
			}
		}
	}
}
