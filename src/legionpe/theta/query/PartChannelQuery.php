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

namespace legionpe\theta\query;

use legionpe\theta\BasePlugin;

class PartChannelQuery extends AsyncQuery{
	/** @var int */
	private $uid;
	/** @var string */
	private $channel;
	public function __construct(BasePlugin $main, $uid, $channel){
		$this->uid = $uid;
		$this->channel = $channel;
		parent::__construct($main);
	}
	public function getResultType(){
		return self::TYPE_RAW;
	}
	public function getQuery(){
		return "DELETE FROM channels WHERE uid=$this->uid AND channel={$this->esc($this->channel)}";
	}
}
