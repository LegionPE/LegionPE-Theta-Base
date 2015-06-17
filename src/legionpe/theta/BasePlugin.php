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
use legionpe\theta\lang\LanguageManager;
use legionpe\theta\lang\Phrases;
use legionpe\theta\query\CloseServerQuery;
use legionpe\theta\query\InitDbQuery;
use legionpe\theta\query\LoginDataQuery;
use legionpe\theta\query\NewUserQuery;
use legionpe\theta\query\SaveSinglePlayerQuery;
use legionpe\theta\query\SearchServerQuery;
use legionpe\theta\queue\Queue;
use legionpe\theta\queue\TransferSearchRunnable;
use legionpe\theta\utils\SessionTickTask;
use legionpe\theta\utils\SyncStatusTask;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use shoghicp\FastTransfer\FastTransfer;

abstract class BasePlugin extends PluginBase{
	const EMAIL_UNVERIFIED = "UNVERIFIED";
	private static $NAME = null;
	/** @var FastTransfer */
	private $FastTransfer;
	/** @var BaseListener */
	private $listener;
	/** @var SessionEventListener */
	protected $sesList;
	/** @var LanguageManager */
	private $langs;
	/** @var Queue[] */
	private $queues = [], $playerQueues = [], $teamQueues = [];
	/** @var Session[] */
	private $sessions = [];
	private $totalPlayers, $maxPlayers, $classTotalPlayers, $classMaxPlayers;
	/** @var string */
	private $altIp;
	/** @var int */
	private $altPort;

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
		$this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new SessionTickTask($this), 1, 20);
		$this->langs = new LanguageManager($this);
	}
	public function onDisable(){
		new CloseServerQuery($this);
	}
	public function evaluate($code){
		eval($code);
	}
	public function getResourceContents($path){
		$handle = $this->getResource($path);
		$r = stream_get_contents($handle);
		fclose($handle);
		return $r;
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
		if($loginData === null){
			$player->sendMessage(Phrases::VAR_wait . "Welcome to Legion PE! Please wait while we are preparing to register an account for you.");
			new NewUserQuery($this, $player);
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
	public function saveSessionData(Session $session, $newStatus = Settings::STATUS_OFFLINE){
		new SaveSinglePlayerQuery($this, $session, $newStatus);
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
			"lastses" => Settings::$LOCALIZE_CLASS,
			"authuuid" => $player->getUniqueId(),
			"coins" => 100.0,
			"hash" => str_repeat("0", 128),
			"pwprefix" => "\0",
			"pwlen" => 0,
			"registration" => time(),
			"laston" => time(),
			"ontime" => 0,
			"config" => Settings::CONFIG_DEFAULT_VALUE,
			"lastgrind" => 0,
			"rank" => 0,
			"warnpts" => 0,
			"tid" => -1,
			"teamrank" => -1,
			"teamjoin" => 0,
			"ignorelist" => ",",
			"iphist" => ",$ip,",
			"isnew" => true,
			"email" => self::EMAIL_UNVERIFIED,
		];
	}

	// override-able implementation classes
	public function getBasicListener(){
		return BaseListener::class;
	}
	protected function getSessionListenerClass(){
		return SessionEventListener::class;
	}
	public abstract function sendFirstJoinMessages(Player $player);
	public abstract function query_world();
	public function getLoginQueryImpl(){
		return LoginDataQuery::class;
	}
	public function getSaveSingleQueryImpl(){
		return SaveSinglePlayerQuery::class;
	}
	/**
	 * @return string|null
	 */
	protected function getServerNameAppend(){
		return null;
	}

	// global-level utils functions
	public function transfer(Player $player, $ip, $port, $msg, $save = true){
		if($save and ($session = $this->getSession($player)) instanceof Session){
			$this->saveSessionData($session, Settings::STATUS_TRANSFERRING);
		}
		$this->FastTransfer->transferPlayer($player, $ip, $port, $msg);
	}
	public function transferGame(Player $player, $class){
		$task = new SearchServerQuery($this, $class);
		$this->queueFor($player->getId(), true, Queue::QUEUE_SESSION)
			->pushToQueue(new TransferSearchRunnable($this, $player, $task));
	}
	public function setPlayerCount($total, $max, $classTotal, $classMax){
		$this->totalPlayers = $total;
		$this->maxPlayers = $max;
		$this->classTotalPlayers = $classTotal;
		$this->classMaxPlayers = $classMax;
		$append = $this->getServerNameAppend();
		$online = count($this->getServer()->getOnlinePlayers());
		$this->getServer()->getNetwork()->setName(
			TextFormat::BOLD . TextFormat::AQUA . "LegionPE " .
			TextFormat::BOLD . TextFormat::GREEN . Settings::$CLASSES_NAMES[Settings::$LOCALIZE_CLASS] .
			TextFormat::RESET . TextFormat::DARK_AQUA . " [$online/$classTotal/$total/$max]" .
			(
				($append === null) ? "" :
				(TextFormat::RESET . TextFormat::GRAY . " - " . TextFormat::RESET . $append)
			)
		);
	}
	public function getPlayersCount(&$total, &$max, &$classTotal, &$classMax){
		$total = $this->totalPlayers;
		$max = $this->maxPlayers;
		$classTotal = $this->classTotalPlayers;
		$classMax = $this->classMaxPlayers;
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
	public function getLangs(){
		return $this->langs;
	}
	/**
	 * @param string &$altIp
	 * @param int &$altPort
	 */
	public function getAltServer(&$altIp, &$altPort){
		$altIp = $this->altIp;
		$altPort = $this->altPort;
	}
	/**
	 * @param string $altIp
	 * @param int $altPort
	 */
	public function setAltServer($altIp, $altPort){
		$this->altIp = $altIp;
		$this->altPort = $altPort;
	}
}
