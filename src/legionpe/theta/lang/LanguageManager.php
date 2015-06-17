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

namespace legionpe\theta\lang;

use legionpe\theta\BasePlugin;

class LanguageManager{
	/** @var Phrase[] */
	private $phrases = [];
	public function __construct(BasePlugin $plugin){
		/** @var string[] $langs */
		$langs = json_decode($plugin->getResourceContents("langs/index.json"), true);
		foreach($langs as $lang){
			$path = "langs/$lang.json";
			$data = json_decode($plugin->getResourceContents($path), true);
			foreach($data as $key => $phrase){
				$this->getPhraseObject($key)->setImplementation($lang, $phrase);
			}
		}
	}
	public function get($key, $vars, ...$langs){
		$langs[] = "en";
		return $this->phrases[$key]->get($vars, $langs);
	}
	public function getPhraseObject($key){
		if(!isset($this->phrases[$key])){
			$this->phrases[$key] = new Phrase($key);
		}
		return $this->phrases[$key];
	}
}
