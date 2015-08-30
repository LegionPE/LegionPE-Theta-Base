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

namespace legionpe\theta\command\session;

use legionpe\theta\BasePlugin;
use legionpe\theta\command\SessionCommand;
use legionpe\theta\lang\Phrases;
use legionpe\theta\Session;

class AltServerCommand extends SessionCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "alt", "Join another server of the same gametype", "/alt", []);
	}
	protected function run(array $args, Session $sender){
		if(!$sender->confirmAlt){
			$sender->confirmAlt = true;
			return $sender->translate(Phrases::CMD_ALT_WARNING);
		}
		$this->getMain()->getAltServer($ip, $port);
		if($ip !== "0.0.0.0"){
			$this->getMain()->transfer($sender->getPlayer(), $ip, $port, $sender->translate(Phrases::CMD_ALT_SUCCESS, [
				"ip" => $ip,
				"port" => $port
			]));
			return true;
		}
		return $sender->translate(Phrases::CMD_ALT_WAIT);
	}
}
