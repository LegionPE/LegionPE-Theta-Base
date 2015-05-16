<?php

namespace legionpe\theta\queue;

interface Runnable{
	public function canRun();
	public function run();
}
