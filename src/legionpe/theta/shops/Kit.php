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

use legionpe\theta\query\DownloadKitQuery;
use legionpe\theta\Session;

class Kit{
	const SLOT_SPECIAL_REAL_SIZE = -1;
	const SLOT_SPECIAL_ABSTRACT_SIZE = -2;
	const SLOT_SPECIAL_NAME = -3;
	const ARMOR_STARTS_AT = 32;
	const ABSTRACT_STARTS_AT = 48;
	/** @var int */
	public $uid, $kitid;
	/** @var string */
	public $name;
	/** @var KitEntry[] */
	public $armorSlots = [], $realSlots = [], $abstractSlots = [];
	/** @var int */
	public $realSize, $abstractSize, $armorSize = 4;
	public static function fromQuery(DownloadKitQuery $query, Session $session){
		$rows = $query->rows;
		$kit = new Kit;
		$kit->uid = $query->uid;
		$kit->kitid = $query->kitid;
		foreach($rows as $row){
			$slot = $row["slot"];
			$name = $row["name"];
			$value = $row["value"];
			if($slot === self::SLOT_SPECIAL_REAL_SIZE){
				$kit->realSize = $value;
			}elseif($slot === self::SLOT_SPECIAL_ABSTRACT_SIZE){
				$kit->abstractSize = $value;
			}elseif($slot === self::SLOT_SPECIAL_NAME){
				$kit->name = $name;
			}elseif($slot < self::ARMOR_STARTS_AT){
				$kit->realSlots[$slot] = new KitEntry($kit, $slot, $name, $value, $session->getPurchase($value));
			}elseif($slot < self::ABSTRACT_STARTS_AT){
				$kit->armorSlots[$slot - self::ARMOR_STARTS_AT] = new KitEntry($kit, $slot, $name, $value, $session->getPurchase($value));
			}else{
				$kit->abstractSlots[$slot - self::ABSTRACT_STARTS_AT] = new KitEntry($kit, $slot, $name, $value, $session->getPurchase($value));
			}
		}
		for($i = 0; $i < $kit->realSize; $i++){
			if(!isset($kit->realSlots[$i])){
				$kit->realSize[$i] = new KitEntry($kit, $i, "Slot " . ($i + 1), 0, null, false);
			}
		}
		for($i = 0; $i < $kit->abstractSize; $i++){
			if(!isset($kit->abstractSlots[$i])){
				$kit->abstractSlots[$i] = new KitEntry($kit, $i + self::ABSTRACT_STARTS_AT, "Slot " . ($i + 1), 0, null, false);
			}
		}
		return $kit;
	}
}
