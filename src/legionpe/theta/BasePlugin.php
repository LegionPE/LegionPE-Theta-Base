<?php

namespace legionpe\theta;

use legionpe\theta\config\Settings;
use legionpe\theta\query\NextIdQuery;
use legionpe\theta\query\ReportStatusQuery;
use legionpe\theta\query\SearchServerQuery;
use legionpe\theta\queue\NewSessionRunnable;
use legionpe\theta\queue\Queue;
use legionpe\theta\queue\TransferSearchRunnable;
use legionpe\theta\utils\BaseListener;
use legionpe\theta\utils\CallbackPluginTask;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use shoghicp\FastTransfer\FastTransfer;

abstract class BasePlugin extends PluginBase{
	/** @var FastTransfer */
	private $FastTransfer;
	/** @var BaseListener */
	private $listener;
	/** @var SessionEventListener */
	protected $sesList;
	/** @var Queue[] */
	private $queues = [], $playerQueues = [], $teamQueues = [];
	/** @var Session[] */
	private $sessions = [];
	public function onEnable(){
		$this->FastTransfer = $this->getServer()->getPluginManager()->getPlugin("FastTransfer");
		$this->getServer()->getPluginManager()->registerEvents($this->listener = new BaseListener($this), $this);
		$class = $this->getSessionListenerClass();
		$this->getServer()->getPluginManager()->registerEvents($this->sesList = new $class($this), $this);
		$this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(
			new CallbackPluginTask($this, function(BasePlugin $me){
				new ReportStatusQuery($me, count($me->getServer()->getOnlinePlayers()));
			}, $this), 40, 40);
	}
	public function queueFor($id, $garbage = false, $flag = Queue::QUEUE_GENERAL){
		$queues =& $this->getQueueByFlag($flag);
		if(!isset($queues[$id])){
			/** @noinspection PhpInternalEntityUsedInspection */
			return $queues[$id] = new Queue($this, $id, $garbage, $flag);
		}
		return $queues[$id];
	}
	public function garbage($id, $flag = Queue::QUEUE_GENERAL){
		unset($this->getQueueByFlag($flag)[$id]);
	}
	public function &getQueueByFlag($flag){
		if($flag === Queue::QUEUE_SESSION){
			return $this->playerQueues;
		}
		if($flag === Queue::QUEUE_TEAM){
			return $this->teamQueues;
		}
		return $this->queues;
	}
	/**
	 * @param Player $player
	 * @param mixed[]|null $loginData
	 * @return bool
	 */
	public function newSession(Player $player, $loginData = null){
		if($loginData === null){
			$task = new NextIdQuery($this, NextIdQuery::USER);
			$this->queueFor($player->getId(), true, Queue::QUEUE_SESSION)->pushToQueue(new NewSessionRunnable($this, $task, $player->getId()));
			return false;
		}
		try{
			$this->sessions[$player->getId()] = $this->createSession($player, $loginData);
			return true;
		}catch(\Exception $e){
			return false;
		}
	}
	public function getSession($player){
		if(is_string($player)){
			$player = $this->getServer()->getPlayer($player);
		}
		if(!($player instanceof Player)){
			return null;
		}
		return isset($this->sessions[$player->getId()]) ? $this->sessions[$player->getId()] : null;
	}
	protected abstract function createSession(Player $player, array $loginData);
	public static function getDefaultLoginData($uid, Player $player){
		$name = $player->getName();
		$ip = $player->getAddress();
		return [
			"uid" => $uid,
			"name" => $name,
			"nicks" => "|$name|",
			"lastip" => $ip,
			"status" => Settings::STATUS_ONLINE,
			"lastses" => Settings::$CLASSES_TABLE[Settings::$LOCALIZE_CLASS],
			"authuuid" => $player->getUniqueId(),
			"coins" => 100.0,
			"hash" => str_repeat("0", 128),
			"registration" => time(),
			"laston" => time(),
			"ontime" => 0,
			"config" => 0,
			"lastgrind" => 0,
			"rank" => 0,
			"warnpts" => 0,
			"tid" => -1,
			"teamrank" => -1,
			"teamjoin" => 0,
			"ignorelist" => ","
		];
	}
	protected function getSessionListenerClass(){
		return SessionEventListener::class;
	}
	public function transfer(Player $player, $ip, $port, $msg){
		$this->FastTransfer->transferPlayer($player, $ip, $port, $msg);
	}
	public function transferGame(Player $player, $class){
		$task = new SearchServerQuery($this, $class);
		$this->queueFor($player->getId(), true, Queue::QUEUE_SESSION)
			->pushToQueue(new TransferSearchRunnable($this, $player, $task));
	}
}
