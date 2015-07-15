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

namespace legionpe\theta\queue;

use legionpe\theta\BasePlugin;
use legionpe\theta\utils\ReportErrorTask;
use pocketmine\scheduler\PluginTask;

class Queue extends PluginTask{
	const QUEUE_GENERAL = 0;
	const QUEUE_SESSION = 1;
	const QUEUE_TEAM = 2;
	const GENERAL_ID_FETCH = 1;
	/** @var BasePlugin */
	private $main;
	/** @var int */
	private $queueId;
	/** @var Runnable[] */
	private $queue = [];
	private $nextScheduled = false;
	/** @var bool */
	private $garbageable;
	private $flag;
	/**
	 * @param BasePlugin $main
	 * @param $queueId
	 * @param $garbageable
	 * @param $flag
	 *
	 * @internal Only use in BasePlugin.php
	 */
	public function __construct(BasePlugin $main, $queueId, $garbageable, $flag){
		parent::__construct($this->main = $main);
		$this->queueId = $queueId;
		$this->garbageable = $garbageable;
		$this->flag = $flag;
	}
	public function onRun($t){
		$this->nextScheduled = false;
		while(isset($this->queue[0])){
			if($this->queue[0]->canRun()){
				/** @var Runnable $runnable */
				$runnable = array_shift($this->queue);
				try{
					$runnable->run();
				}catch(\Exception $e){
					$this->getMain()->getServer()->getScheduler()->scheduleAsyncTask(new ReportErrorTask($e, "queue $this->queueId execution of runnable " . get_class($runnable)));
					$this->scheduleNext();
					return;
				}
			}else{
				$this->scheduleNext();
				return;
			}
		}
		if($this->garbageable and !isset($this->queue[0])){
			$this->main->garbageQueue($this->getQueueId(), $this->flag);
		}
	}
	/**
	 * @return BasePlugin
	 */
	public function getMain(){
		return $this->main;
	}
	protected function scheduleNext(){
		if($this->nextScheduled){
			return;
		}
		$this->main->getServer()->getScheduler()->scheduleDelayedTask($this, 1);
		$this->nextScheduled = true;
	}
	/**
	 * @return int
	 */
	public function getQueueId(){
		return $this->queueId;
	}
	public function pushToQueue(Runnable $runnable){
		$this->queue[] = $runnable;
		$this->scheduleNext();
		$this->getMain()->getLogger()->debug("Pushed " . get_class($runnable) . " to queue " . $this->flag . "#" . $this->queueId);
	}
	public function __debugInfo(){
		return [
			"queueId" => $this->queueId,
			"queue" => $this->queue,
			"nextScheduled" => $this->nextScheduled,
			"garbageable" => $this->garbageable,
			"flagname" => array_search($this->flag, (new \ReflectionClass($this))->getConstants())
		];
	}
	/**
	 * @return Runnable[]
	 */
	public function getQueue(){
		return $this->queue;
	}
}
