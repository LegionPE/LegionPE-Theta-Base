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

class PushChatQuery extends AsyncQuery{
	private $src;
	private $msg;
	private $type;
	private $class;
	private $data;
	public function __construct(BasePlugin $main, $src, $msg, $type, $class, $data = []){
		$this->src = $src;
		$this->msg = $msg;
		$this->type = $type;
		$this->class = $class;
		$this->data = json_encode($data);
		parent::__construct($main);
	}
	public function getQuery(){
		return "INSERT INTO chat(src,msg,type,class,json)VALUES({$this->esc($this->src)},{$this->esc($this->msg)},$this->type,$this->class,{$this->esc($this->data)})";
	}
	public function getResultType(){
		return self::TYPE_RAW;
	}
}
