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
use legionpe\theta\chat\Hormone;
use pocketmine\Server;

class PushChatQuery extends AsyncQuery{
	private $src;
	private $msg;
	private $type;
	private $class;
	private $data;
	private $rowId;
	/** @var Hormone|null */
	private $hormone;
	public function __construct(BasePlugin $main, $src, $msg, $type, $class, $data = [], $hormone = null){
		$this->src = $src;
		$this->msg = $msg;
		$this->type = $type;
		$this->class = $class;
		$this->data = json_encode($data);
		$this->hormone = $main->storeObject($hormone);
		parent::__construct($main);
	}
	public function getQuery(){
		return "INSERT INTO chat(src,msg,type,class,json)VALUES({$this->esc($this->src)},{$this->esc($this->msg)},$this->type,$this->class,{$this->esc($this->data)})";
	}
	public function onPostQuery(\mysqli $db){
		$this->rowId = $db->insert_id;
	}
	public function getResultType(){
		return self::TYPE_RAW;
	}
	public function reportDebug(){
		return false;
	}
	/**
	 * @return mixed
	 */
	public function getRowId(){
		return $this->rowId;
	}
	public function onCompletion(Server $server){
		$main = BasePlugin::getInstance($server);
		$hormone = $main->fetchObject($this->hormone);
		if($hormone instanceof Hormone){
			$hormone->onPostRelease($this->rowId);
		}
	}
}
