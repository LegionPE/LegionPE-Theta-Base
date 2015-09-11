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

namespace legionpe\theta\chat;

use legionpe\theta\BasePlugin;
use legionpe\theta\MuteIssue;

class MuteHormone extends Hormone{
	protected $since;
	protected $length;
	protected $uid;
	protected $ip;
	protected $cid;
	public static function fromObject(BasePlugin $main, MuteIssue $mute){
		return new MuteHormone($main, $mute->src, $mute->msg, 0, [
			"since" => $mute->since,
			"length" => $mute->length,
			"uid" => $mute->uid,
			"ip" => $mute->ip,
			"cid" => $mute->cid,
		]); // change 0 if issuer is sectional moderator
	}
	public function getType(){
		return self::MUTE_CHAT;
	}
	public function execute(){
		$issue = new MuteIssue;
		$issue->since = $this->since;
		$issue->length = $this->length;
		$issue->uid = $this->uid;
		$issue->ip = $this->ip;
		$issue->cid = $this->cid;
		$issue->msg = $this->msg;
		$issue->src = $this->src;
		$this->main->addMute($issue);
	}
}
