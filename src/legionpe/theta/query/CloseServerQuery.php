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

namespace legionpe\theta\query;

use legionpe\theta\config\Settings;

class CloseServerQuery extends AsyncQuery{
	public function getQuery(){
		return "UPDATE server_status SET last_online=0 WHERE server_id=" . Settings::$LOCALIZE_ID;
	}
	public function getResultType(){
		return self::TYPE_RAW;
	}
	public function onRun(){
		parent::onRun();
		$this->getConn()->close();
	}
}
