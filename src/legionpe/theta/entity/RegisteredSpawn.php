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

namespace legionpe\theta\entity;

use pocketmine\math\Vector3;

class RegisteredSpawn extends Vector3{
	/** @var int */
	private $id;
	/** @var string */
	private $class;
	/** @var mixed */
	private $data;
	public function __construct($x, $y, $z, $id, $class, $data){
		parent::__construct($x, $y, $z);
		$this->id = $id;
		$this->class = $class;
		$this->data = $data;
	}
	public function getId(){
		return $this->id;
	}
	/**
	 * @return string
	 */
	public function getClass(){
		return $this->class;
	}
	/**
	 * @return mixed
	 */
	public function getData(){
		return $this->data;
	}
}
