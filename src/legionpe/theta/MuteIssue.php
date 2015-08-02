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

namespace legionpe\theta;

use legionpe\theta\lang\Phrases;
use legionpe\theta\utils\MUtils;

class MuteIssue{
	public $since;
	public $length;
	public $uid;
	public $ip;
	public $cid;
	public $msg;
	public $src;
	public function sendToSession(Session $session){
		$session->send(Phrases::WARNING_MUTED_NOTIFICATION, [
			"length" => MUtils::time_secsToString($this->length),
			"since" => date($session->translate("date.format"), $this->since),
			"till" => date($session->translate("date.format"), $this->since + $this->length),
			"passed" => MUtils::time_secsToString(time() - $this->since),
			"left" => MUtils::time_secsToString($this->since + $this->length - time()),
		]);
	}
}
