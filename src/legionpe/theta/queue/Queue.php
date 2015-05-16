<?php

namespace legionpe\theta\queue;

use legionpe\theta\BasePlugin;
use legionpe\theta\utils\ReportErrorTask;
use pocketmine\scheduler\PluginTask;

class Queue extends PluginTask{
	const QUEUE_GENERAL = 0;
	const QUEUE_SESSION = 1;
	const QUEUE_TEAM = 2;
	/** @var BasePlugin */
	private $main;
	/** @var int */
	private $queueId;
	/** @var Runnable[] */
	private $queue = [];
	private $nextScheduled = false;
	/** @var bool */
	private $garbageable;
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
		if($this->garbageable){
			$this->main->garbage($this->getQueueId());
		}
	}
	public function pushToQueue(Runnable $runnable){
		$this->queue[] = $runnable;
		$this->scheduleNext();
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
	/**
	 * @return BasePlugin
	 */
	public function getMain(){
		return $this->main;
	}
}
