<?php

/**
 * LegionPE
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

namespace legionpe\theta;

use legionpe\theta\command\ThetaCommand;
use legionpe\theta\config\Settings;
use legionpe\theta\query\CloseServerQuery;
use legionpe\theta\query\InitDbQuery;
use legionpe\theta\query\NextIdQuery;
use legionpe\theta\query\SearchServerQuery;
use legionpe\theta\queue\NewSessionRunnable;
use legionpe\theta\queue\Queue;
use legionpe\theta\queue\TransferSearchRunnable;
use legionpe\theta\utils\SyncStatusTask;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use shoghicp\FastTransfer\FastTransfer;

abstract class BasePlugin extends PluginBase{
	private static $NAME = null;
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
	private $totalPlayers, $maxPlayers;

	// PluginManager-level stuff
	/**
	 * @param Server $server
	 * @return static
	 */
	public static function getInstance(Server $server){
		return $server->getPluginManager()->getPlugin(self::$NAME);
	}
	public function onLoad(){
		self::$NAME = $this->getName();
		class_exists(CloseServerQuery::class); // preload to workaround frequent corruption errors due to phar repalced
	}
	public function onEnable(){
		ThetaCommand::registerAll($this, $this->getServer()->getCommandMap());
		$this->FastTransfer = $this->getServer()->getPluginManager()->getPlugin("FastTransfer");
		$class = $this->getBasicListener();
		$this->getServer()->getPluginManager()->registerEvents($this->listener = new $class($this), $this);
		$class = $this->getSessionListenerClass();
		$this->getServer()->getPluginManager()->registerEvents($this->sesList = new $class($this), $this);
		new InitDbQuery($this);
		$this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new SyncStatusTask($this), 40, 40);
	}
	public function onDisable(){
		new CloseServerQuery($this);
	}
	public function evaluate($code){
		eval($code);
	}

	// queues
	public function queueFor($id, $garbage = false, $flag = Queue::QUEUE_GENERAL){
		$queues =& $this->getQueueByFlag($flag);
		if(!isset($queues[$id])){
			/** @noinspection PhpInternalEntityUsedInspection */
			return $queues[$id] = new Queue($this, $id, $garbage, $flag);
		}
		return $queues[$id];
	}
	public function garbageQueue($id, $flag = Queue::QUEUE_GENERAL){
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

	// session stuff
	/**
	 * @param Player $player
	 * @param mixed[]|null $loginData
	 * @return bool
	 */
	public function newSession(Player $player, $loginData = null){
		$this->getLogger()->debug((new \Exception)->getTraceAsString());
		if($loginData === null){
			$player->sendMessage(TextFormat::AQUA . "Welcome to Legion PE! Please wait while we are preparing to register an account for you.");
			$task = new NextIdQuery($this, NextIdQuery::USER);
			$this->queueFor(Queue::GENERAL_ID_FETCH, true)->pushToQueue(new NewSessionRunnable($this, $task, $player->getId()));
			return false;
		}
		try{
			$this->sessions[$player->getId()] = $this->createSession($player, $loginData);
			return true;
		}catch(\Exception $e){
			return false;
		}
	}
	public function endSession(Player $player){
		if(isset($this->playerQueues[$player->getId()])){
			unset($this->playerQueues[$player->getId()]);
		}
		if(isset($this->sessions[$player->getId()])){
			$this->sessions[$player->getId()]->onQuit();
			unset($this->sessions[$player->getId()]);
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
	public function getSessions(){
		return $this->sessions;
	}
	protected abstract function createSession(Player $player, array $loginData);
	public static function getDefaultLoginData($uid, Player $player){
		$name = $player->getName();
		$ip = $player->getAddress();
		return [
			"uid" => $uid,
			"name" => $name,
			"nicks" => "|$name|",
			"lastip" => "",
			"status" => Settings::STATUS_ONLINE,
			"lastses" => Settings::$CLASSES_TABLE[Settings::$LOCALIZE_CLASS],
			"authuuid" => $player->getUniqueId(),
			"coins" => 100.0,
			"hash" => str_repeat("0", 128),
			"pwprefix" => "\0",
			"pwlen" => 0,
			"registration" => time(),
			"laston" => time(),
			"ontime" => 0,
			"config" => Settings::CONFIG_AUTH_NONE,
			"lastgrind" => 0,
			"rank" => 0,
			"warnpts" => 0,
			"tid" => -1,
			"teamrank" => -1,
			"teamjoin" => 0,
			"ignorelist" => ",",
			"iphist" => ",$ip,",
			"isnew" => true
		];
	}

	// overridable implementation classes
	public function getBasicListener(){
		return BaseListener::class;
	}
	protected function getSessionListenerClass(){
		return SessionEventListener::class;
	}
	public abstract function sendFirstJoinMessages(Player $player);
	public abstract function query_world();

	// global-level utils functions
	public function transfer(Player $player, $ip, $port, $msg){
		$this->FastTransfer->transferPlayer($player, $ip, $port, $msg);
	}
	public function transferGame(Player $player, $class){
		$task = new SearchServerQuery($this, $class);
		$this->queueFor($player->getId(), true, Queue::QUEUE_SESSION)
			->pushToQueue(new TransferSearchRunnable($this, $player, $task));
	}
	public function setPlayerCount($total, $max){
		$this->totalPlayers = $total;
		$this->maxPlayers = $max;
		$this->getServer()->getNetwork()->setName(TextFormat::AQUA . "LegionPE " . TextFormat::GREEN . "PE " . TextFormat::LIGHT_PURPLE . "[$total / $max] " . TextFormat::RED . "{TPS {$this->getServer()->getTicksPerSecond()}}");
	}
	public function getPlayersCount(&$total, &$max){
		$total = $this->totalPlayers;
		$max = $this->maxPlayers;
	}

	// public getters
	/**
	 * @return BaseListener
	 */
	public function getListener(){
		return $this->listener;
	}
	/**
	 * @return SessionEventListener
	 */
	public function getSesList(){
		return $this->sesList;
	}
}
