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
	public function hasExpired(){
		return time() > $this->expiry;
	}
}
