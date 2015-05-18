<?php

namespace legionpe\theta\config;

class WarnPtsConseq{
	public $origin;
	public $muteSecs;
	public $banLength;
	public function __construct($muteSecs = 0, $banLength = 0, $origin = null){
		$this->muteSecs = $muteSecs;
		$this->banLength = $banLength;
		$this->setOrigin($origin);
	}
	public function setOrigin($origin = null){
		if($origin === null){
			$origin = time();
		}
		$this->origin = $origin;
		$diff = time() - $origin;
		$this->muteSecs -= $diff;
		$this->banLength -= $diff;
	}
}
