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

namespace legionpe\theta\command\override;

use legionpe\theta\BasePlugin;
use legionpe\theta\command\SessionCommand;
use legionpe\theta\Session;

class OverridingMeCommand extends SessionCommand{
	public function __construct(BasePlugin $main){
		parent::__construct($main, "me", "Chat", "/me <message>");
	}
	protected function run(array $args, Session $sender){
		$msg = implode(" ", $args);
		if($sender->getSpamDetector()->censor($msg)){
			$sender->onChat($msg, Session::CHAT_ME);
		}
	}
}
