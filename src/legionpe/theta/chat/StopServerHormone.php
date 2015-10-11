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

namespace legionpe\theta\chat;

class StopServerHormone extends Hormone{
	public function getType(){
		return self::STOP_SERVER_HORMONE;
	}
	public function execute(){
		$this->main->getServer()->shutdown();
	}
}
