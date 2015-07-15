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

class SubmitLabelQuery extends NextIdQuery{
	/** @var string */
	private $value;
	public function __construct(BasePlugin $main, $value){
		$this->value = $value;
		parent::__construct($main, self::LABEL);
	}
	public function onAssocFetched(\mysqli $db, &$row){
		$db->query("INSERT INTO labels(lid, value)VALUES({$this->getId()}, {$this->esc($this->value)})");
	}
}
