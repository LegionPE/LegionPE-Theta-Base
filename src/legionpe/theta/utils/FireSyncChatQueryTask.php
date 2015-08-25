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

use legionpe\theta\query\SyncChatQuery;
use pocketmine\scheduler\PluginTask;

class FireSyncChatQueryTask extends PluginTask{
	public $canFireNext = true;
	public $lastTenDiff = [];
	private $lastFire;
	public function onRun($currentTick){
		if($this->canFireNext){
			if(isset($this->lastFire)){
				$this->lastTenDiff[] = microtime(true) - $this->lastFire;
				if(count($this->lastTenDiff) > 10){
					array_shift($this->lastTenDiff);
				}
			}
			/** @noinspection PhpParamsInspection */
			new SyncChatQuery($this->getOwner(), $this);
			$this->lastFire = microtime(true);
		}
	}
	public function getFrequency(){
		return array_sum($this->lastTenDiff) / count($this->lastTenDiff);
	}
}
