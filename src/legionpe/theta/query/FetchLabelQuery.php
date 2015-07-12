<?php

/**
 * Theta
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

use legionpe\theta\BasePlugin;
use legionpe\theta\config\Settings;
use legionpe\theta\lang\Phrases;
use legionpe\theta\Session;
use pocketmine\Server;

class FetchLabelQuery extends AsyncQuery{
	/** @var string */
	private $name;
	/** @var int */
	private $uid;
	public function __construct(BasePlugin $main, $name, Session $session){
		$this->name = $name;
		$this->uid = $session->getUid();
		parent::__construct($main);
	}
	public function getResultType(){
		return self::TYPE_ASSOC;
	}
	public function getQuery(){
		return "SELECT lid,value,approved FROM labels WHERE lid=$this->name";
	}
	public function onCompletion(Server $server){
		$session = BasePlugin::getInstance($server)->getSessionByUid($this->uid);
		if(!($session instanceof Session)){
			return;
		}
		$result = $this->getResult();
		if($result["type"] === self::TYPE_ASSOC){
			if($session->canUseLabel($result["approved"])){
				$session->setLoginDatum("lbl", $result["value"]);
				new SetLabelQuery($session, $result["lid"]);
			}elseif($result["approved"] === Settings::LABEL_APPROVED_NOT){
				$session->send(Phrases::CMD_LABEL_WAIT_FOR_APPROVAL);
			}
		}
	}
}
