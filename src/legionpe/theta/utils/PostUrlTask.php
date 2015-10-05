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

namespace legionpe\theta\utils;

use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Utils;

class PostUrlTask extends AsyncTask{
	/** @var string */
	private $url;
	/** @var mixed */
	private $postData;
	/** @var string[] */
	private $headers;
	public function __construct($url, $postData, array $headers = []){
		$this->url = $url;
		$this->postData = $postData;
		$this->headers = $headers;
	}
	public function onRun(){
		Utils::postURL($this->url, $this->postData, 10, $this->headers);
	}
}
