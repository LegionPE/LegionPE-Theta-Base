<?php

/**
 * LegionPE
 * Copyright (C) 2015 PEMapModder
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
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
