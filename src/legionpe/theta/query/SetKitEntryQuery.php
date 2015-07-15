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
use legionpe\theta\config\Settings;
use legionpe\theta\shops\KitEntry;

class SetKitEntryQuery extends AsyncQuery{
	/** @var string */
	private $query;
	/**
	 * @param BasePlugin $main
	 * @param KitEntry $entry
	 */
	public function __construct(BasePlugin $main, KitEntry $entry){
		$class = Settings::$LOCALIZE_CLASS;
		$this->query = "UPDATE kits_slots SET name={$this->esc($entry->getName())},value={$entry->getValue()} WHERE uid={$entry->getKit()->uid} AND class=$class AND kitid={$entry->getKit()->kitid} AND slot={$entry->getSlot()}";
		parent::__construct($main);
	}
	public function getQuery(){
		return $this->query;
	}
	public function getResultType(){
		return self::TYPE_RAW;
	}
}
