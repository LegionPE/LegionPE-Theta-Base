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

use legionpe\theta\credentials\Credentials;
use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Utils;

class PostIRCMessageTask extends AsyncTask{
	/** @var string */
	private $msg;
	public function __construct($msg){
		$this->msg = $msg;
	}
	public function onRun(){
		Utils::postURL(Credentials::IRC_WEBHOOK_NOPREFIX . urlencode($this->msg), ["payload" => $this->msg]);
	}
}
