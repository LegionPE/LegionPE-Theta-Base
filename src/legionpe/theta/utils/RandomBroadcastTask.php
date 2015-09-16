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

use legionpe\theta\BasePlugin;
use legionpe\theta\query\RandomBroadcastQuery;
use pocketmine\scheduler\PluginTask;

class RandomBroadcastTask extends PluginTask{
	public function onRun($currentTick){
		/** @var BasePlugin $owner */
		$owner = $this->owner;
		new RandomBroadcastQuery($owner);
	}
}
