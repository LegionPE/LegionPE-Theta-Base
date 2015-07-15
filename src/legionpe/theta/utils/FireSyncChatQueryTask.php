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
	public function onRun($currentTick){
		if($this->canFireNext){
			/** @noinspection PhpParamsInspection */
			new SyncChatQuery($this->getOwner(), $this);
		}
	}
}
