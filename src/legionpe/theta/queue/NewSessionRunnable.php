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

namespace legionpe\theta\queue;

use legionpe\theta\BasePlugin;
use legionpe\theta\query\NextIdQuery;

class NewSessionRunnable implements Runnable{
	/** @var BasePlugin */
	private $main;
	/** @var NextIdQuery */
	private $query;
	/** @var int */
	private $sesId;
	public function __construct(BasePlugin $plugin, NextIdQuery $query, $sesId){
		$this->main = $plugin;
		$this->query = $query;
		$this->sesId = $sesId;
	}
	public function canRun(){
		if($this->query->hasResult()){
			$this->main->getLogger()->info("Can run!");
		}
		return $this->query->hasResult();
	}
	public function run(){
		$uid = $this->query->getResult()["id"];
		foreach($this->main->getServer()->getOnlinePlayers() as $player){
			if($player->getId() === $this->sesId){
				break;
			}
		}
		if(!isset($player)){
			return;
		}
		$this->main->newSession($player, BasePlugin::getDefaultLoginData($uid, $player));
	}
	public function __debugInfo(){
		return [
			"sesId" => $this->sesId
		];
	}
	/**
	 * @return NextIdQuery
	 */
	public function getQuery(){
		return $this->query;
	}
	/**
	 * @return int
	 */
	public function getSesId(){
		return $this->sesId;
	}
}
