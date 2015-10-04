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

namespace legionpe\theta\query;

use legionpe\theta\BasePlugin;
use legionpe\theta\Session;
use pocketmine\Server;

abstract class CommandQuery extends AsyncQuery{
	private $session;
	public function __construct(BasePlugin $main, Session $session){
		$this->session = $main->storeObject($session);
		parent::__construct($main);
	}
	public function onCompletion(Server $server){
		$main = BasePlugin::getInstance($server);
		$ses = $this->getSession($main);
		if(!$ses->getPlayer()->isOnline()){
			return;
		}
		$result = $this->getResult();
		if($result["resulttype"] !== $this->getResultType()){
			$this->onNotFetched($main, $ses);
			return;
		}
		$this->finalize($main, $ses, $result);
	}
	/**
	 * @param BasePlugin $main
	 * @return Session
	 */
	public function getSession(BasePlugin $main){
		return $main->fetchObject($this->session);
	}
	public abstract function finalize(BasePlugin $main, Session $session, $result);
	public function onNotFetched(BasePlugin $main, Session $session){
	}
}
