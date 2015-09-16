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
use legionpe\theta\lang\Phrases;
use pocketmine\Server;

class RandomBroadcastQuery extends AsyncQuery{
	public function getResultType(){
		return self::TYPE_ASSOC;
	}
	public function getQuery(){
		return "SELECT * FROM broadcasts WHERE id >= (SELECT FLOOR( MAX(id) * RAND()) FROM broadcasts) + 1 ORDER BY id LIMIT 1";
	}
	public function getExpectedColumns(){
		return ["id" => self::COL_INT, "en" => self::COL_STRING];
	}
	public function onCompletion(Server $server){
		$result = $this->getResult()["result"];
		if(!is_array($result)){
			return;
		}
		$codes = [];
		$Phrases = new \ReflectionClass(Phrases::class);
		foreach($Phrases->getConstants() as $name => $value){
			if(substr($name, 0, 4) === "VAR_"){
				$codes[substr($name, 4)] = $value;
			}
		}
		foreach($result as &$string){
			foreach($Phrases as $code => $replace){
				$string = str_replace("%$code%", $replace, $string);
			}
		}
		foreach(BasePlugin::getInstance($server)->getSessions() as $ses){
			$langs = $ses->getLangs();
			foreach($langs as $lang){
				if(isset($result[$lang])){
					$ses->getPlayer()->sendTip(Phrases::VAR_notify . $result[$lang]);
				}
			}
		}
	}
}
