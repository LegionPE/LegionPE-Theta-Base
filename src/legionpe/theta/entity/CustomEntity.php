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

use pocketmine\level\format\FullChunk;
use pocketmine\math\Vector3;

interface CustomEntity{
	/**
	 * @param Vector3 $v
	 * @param mixed $data
	 * @param int $id
	 * @param FullChunk $chunk
	 */
	public function __construct(Vector3 $v, $data, $id, FullChunk $chunk);
}
