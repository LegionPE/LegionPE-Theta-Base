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

namespace legionpe\theta\command\session;

use legionpe\theta\BasePlugin;
use legionpe\theta\command\SessionCommand;
use legionpe\theta\miscellaneous\walkingparticle\WalkingParticle;
use legionpe\theta\Session;
use pocketmine\block\Block;
use pocketmine\utils\TextFormat;

class WalkingParticleCommand extends SessionCommand{
	public function __construct(BasePlugin $plugin){
		parent::__construct($plugin, "walkingparticle", "Add a walking particle to your session.", "/walkingparticle <clear|add|list>");
	}
	protected function run(array $args, Session $sender){
		if(!isset($args[0])){
			return false;
		}
		switch($args[0]){
			case "clear":
				$sender->walkingParticles = [];
				return TextFormat::GREEN . "Walking particles have been cleared.";
				break;
			case "add":
				if($sender->isVIP()){
					if(count($sender->walkingParticles) >= 2) return TextFormat::RED . "You are already using 2 walking particles. Clear them to add new ones.";
				}
				if($sender->isDonator() and !$sender->isVIP()){
					if(count($sender->walkingParticles) >= 1) return TextFormat::RED . "Please upgrade to VIP to add up to 2 walking particles.";
				}
				if(!isset($args[1])){
					return TextFormat::AQUA . "Available walking particles: " . WalkingParticle::getAllowedWalkingParticleNames($sender);
				}
				if(!isset(WalkingParticle::$ids[$args[1]])){
					return TextFormat::RED . "Walking particle doesn't exist.";
				}
				if(!WalkingParticle::canUseWalkingParticle($sender, WalkingParticle::$ids[$args[1]])){
					return TextFormat::RED . "You do not have access to this walking particle.";
				}
				$class = WalkingParticle::$classes[WalkingParticle::$ids[$args[1]]];
				$sender->walkingParticles[] = $wp = new $class($sender);
				return TextFormat::GREEN . $wp->getName() . " has been added to your current walking particles.";
				break;
			case "list":
				return TextFormat::AQUA . "Available walking particles: " . WalkingParticle::getAllowedWalkingParticleNames($sender);
				break;
		}
	}
	protected function checkPerm(Session $session, &$msg = ""){
		if(!$session->isDonator()){
			$msg = "This command is only for donators.";
			return false;
		}
		return true;
	}
}
