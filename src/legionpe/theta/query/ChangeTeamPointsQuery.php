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

class ChangeTeamPointsQuery extends AsyncQuery{
	private $tid;
	private $points;
	public function __construct(BasePlugin $main, $tid, $points){
		$this->tid = $tid;
		$this->points = $points;
		parent::__construct($main);
	}
	public function getResultType(){
		return self::TYPE_RAW;
	}
	public function getQuery(){
		return "UPDATE teams SET points = points " . ($this->points > 0 ? "+" : "-") . " $this->points WHERE tid=$this->tid";
	}
}
