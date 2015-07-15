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

use legionpe\theta\query\ReportStatusQuery;
use pocketmine\scheduler\PluginTask;

class SyncStatusTask extends PluginTask{
	public function onRun($currentTick){
		/** @noinspection PhpParamsInspection */
		new ReportStatusQuery($this->getOwner());
	}
}
