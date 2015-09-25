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

namespace legionpe\theta\config;

class WarnPtsConsequence{
	public $origin;
	public $muteSecs;
	public $banLength;
	public function __construct($muteSecs = 0, $banLength = 0, $origin = null){
		$this->muteSecs = $muteSecs;
		$this->banLength = $banLength;
		$this->setOrigin($origin);
	}
	public function setOrigin($origin = null){
		if($origin === null){
			$origin = time();
		}
		$this->origin = $origin;
		$diff = time() - $origin;
		$this->muteSecs -= $diff;
		$this->banLength -= $diff;
	}
}
