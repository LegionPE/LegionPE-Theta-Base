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

namespace legionpe\theta\queue;

use legionpe\theta\BasePlugin;
use legionpe\theta\Session;
use pocketmine\Player;

class JoinTriggerRunnable implements Runnable{
	/** @var BasePlugin */
	private $main;
	/** @var Player */
	private $player;
	/** @var Session */
	private $session;
	public function __construct(BasePlugin $main, Player $player){
		$this->main = $main;
		$this->player = $player;
	}
	public function canRun(){
		return ($this->session = $this->main->getSession($this->player)) instanceof Session;
	}
	public function run(){
		$this->session->onJoin();
	}
	public function __debugInfo(){
		return ["session" => $this->session];
	}
}
