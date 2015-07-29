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

namespace legionpe\theta\shops;

class Purchase{
	/** @var int */
	private $pid;
	/** @var int */
	private $uid;
	/** @var int */
	private $class;
	/** @var int */
	private $id;
	/** @var int */
	private $amplitude;
	/** @var int */
	private $count;
	/** @var int */
	private $expiry;
	public function __construct($pid, $uid, $class, $id, $amplitude, $count, $expiry){
		$this->pid = $pid;
		$this->uid = $uid;
		$this->class = $class;
		$this->id = $id;
		$this->amplitude = $amplitude;
		$this->count = $count;
		$this->expiry = $expiry;
	}
	/**
	 * @return int
	 */
	public function getOwner(){
		return $this->uid;
	}
	/**
	 * @return int
	 */
	public function getClass(){
		return $this->class;
	}
	/**
	 * @return int
	 */
	public function getId(){
		return $this->id;
	}
	/**
	 * @return int
	 */
	public function getAmplitude(){
		return $this->amplitude;
	}
	/**
	 * @return int
	 */
	public function getCount(){
		return $this->count;
	}
	/**
	 * @return int
	 */
	public function getExpiry(){
		return $this->expiry;
	}
	/**
	 * @return bool
	 */
	public function hasExpired(){
		return time() > $this->expiry;
	}
	/**
	 * @return int
	 */
	public function getPurchaseId(){
		return $this->pid;
	}
}
