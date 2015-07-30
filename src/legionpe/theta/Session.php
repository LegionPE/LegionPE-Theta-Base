<?php

#define $this->getLoginDatum(key) (isset($this->loginData[key]) ? $this->loginData[key] : null)

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

namespace legionpe\theta;

use legionpe\theta\chat\ChannelChatType;
use legionpe\theta\chat\MuteChatType;
use legionpe\theta\chat\SpamDetector;
use legionpe\theta\chat\TeamChatType;
use legionpe\theta\config\Settings;
use legionpe\theta\lang\Phrases;
use legionpe\theta\query\AddIpQuery;
use legionpe\theta\query\JoinChannelQuery;
use legionpe\theta\query\PartChannelQuery;
use legionpe\theta\query\PreExecuteWarningQuery;
use legionpe\theta\query\RawAsyncQuery;
use legionpe\theta\utils\MUtils;
use pocketmine\block\Block;
use pocketmine\command\CommandSender;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\inventory\InventoryPickupArrowEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\TextContainer;
use pocketmine\level\particle\TerrainParticle;
use pocketmine\level\sound\FizzSound;
use pocketmine\Player;
use pocketmine\utils\Random;
use pocketmine\utils\TextFormat;

abstract class Session{
	const AUTH_TRANSFER = 0;
	const AUTH_UUID = 1;
	const AUTH_IP_LAST = 2;
	const AUTH_IP_HIST = 3;
	const AUTH_SUBNET_LAST = 4;
	/** @deprecated */
	const AUTH_SUBNET_HIST = 5;
	const AUTH_PASS = 6;
	const AUTH_REG = 7;
	const STATE_LOADING = 0x00;
	const STATE_REGISTERING = 0x10;
	const STATE_REGISTERING_FIRST = self::STATE_REGISTERING;
	const STATE_REGISTERING_SECOND = self::STATE_REGISTERING | 0x01;
	const STATE_LOGIN = 0x20;
	const STATE_UPDATE_HASH = 0x30;
	const STATE_PLAYING = 0x40;
	const STATE_TRANSFERRING = 0x80;
	const CHAT_STD = 0;
	const CHAT_ME = 1;
	const CHAT_LOCAL = 2;
	const CHANNEL_LOCAL = "&local";
	const CHANNEL_TEAM = "&team";
	const CHANNEL_SUB_VERBOSE = 0;
	const CHANNEL_SUB_NORMAL = 1;
	const CHANNEL_SUB_ALERT = 2;
	const FRIEND_BITMASK_REQUEST = 0b11;
	const FRIEND_REQUEST_BITS = 2;
	const FRIEND_REQUEST_FROM_SMALL = 0b01;
	const FRIEND_REQUEST_TO_LARGE = 0b01;
	const FRIEND_REQUEST_FROM_LARGE = 0b10;
	const FRIEND_REQUEST_TO_SMALL = 0b10;
	const FRIEND_LEVEL_NONE = 0b000100;
	const FRIEND_LEVEL_ACQUAINTANCE = 0b001000;
	const FRIEND_LEVEL_GOOD_FRIEND = 0b010000;
	const FRIEND_LEVEL_BEST_FRIEND = 0b100000;
	const FRIEND_LEVEL_MAX = self::FRIEND_LEVEL_BEST_FRIEND;
	const FRIEND_NO_REQUEST = 4;
	const FRIEND_IN = 1;
	const FRIEND_OUT = 2;
	public static $AUTH_METHODS = [
		self::AUTH_TRANSFER => "transferring",
		self::AUTH_UUID => "matching unique ID",
		self::AUTH_IP_LAST => "matching last IP",
		self::AUTH_IP_HIST => "matching IP history",
		self::AUTH_PASS => "matching password",
		self::AUTH_REG => "registering"
	];
	public static $AUTH_METHODS_PHRASES = [
		self::AUTH_TRANSFER => "login.auth.method.transfer",
		self::AUTH_UUID => "login.auth.method.uuid",
		self::AUTH_IP_LAST => "login.auth.method.ip.last",
		self::AUTH_IP_HIST => "login.auth.method.ip.hist",
		self::AUTH_PASS => "login.auth.method.pass",
		self::AUTH_REG => "login.auth.method.register"
	];
	public static $FRIEND_TYPES = [
		self::FRIEND_LEVEL_ACQUAINTANCE => Phrases::FRIEND_ACQUAINTANCE,
		self::FRIEND_LEVEL_GOOD_FRIEND => Phrases::FRIEND_GOOD_FRIEND,
		self::FRIEND_LEVEL_BEST_FRIEND => Phrases::FRIEND_BEST_FRIEND
	];
	public $currentChatState = self::CHANNEL_LOCAL;
	/** @var Player */
	private $player;
	/** @var mixed[] */
	private $loginData;
	/** @var string */
	private $inGameName = null;
	/** @var int */
	private $joinTime;
	/** @var float */
	private $coinsOld = 0;
	/** @var float */
	private $ontimeSince;
	/** @var SpamDetector */
	private $spamDetector;
	/** @var int */
	private $state = self::STATE_LOADING;
	/** @var bool */
	public $confirmGrind = false, $confirmQuitTeam = false;
	private $invisibleFrom = [];
	/** @var string|TextContainer|null */
	private $tmpHash = null, $curPopup = null;
	/** @var int half seconds until #postOnline */
	private $postOnlineTimeout = Settings::POST_ONLINE_FREQUENCY;
	public function __construct(Player $player, $loginData){
		$this->player = $player;
		$this->loginData = $loginData;
		$this->joinTime = time();
		$this->coinsOld = $loginData["coins"];
		$this->ontimeSince = microtime(true);
		if($this->init() === false){
			throw new \Exception;
		}
	}
	protected function init(){
		$conseq = $this->getEffectiveConseq();
		if($conseq->banLength > 0){
			$left = MUtils::time_secsToString($conseq->banLength);
			$this->getPlayer()->kick(TextFormat::RED . "You are banned.\nYou have accumulated " . TextFormat::DARK_PURPLE . $this->getWarningPoints() . TextFormat::RED . " warning points,\nand you still have " . TextFormat::BLUE . $left . TextFormat::RED . " before you are unbanned.\n" . TextFormat::AQUA . "Believe this to be a mistake? Email us at " . TextFormat::DARK_PURPLE . "support@legionpvp.eu");
			return false;
		}
		$this->spamDetector = new SpamDetector($this);
		return true;
	}
	public function getEffectiveConseq(){
		return Settings::getWarnPtsConseq($this->getWarningPoints(), $this->getLastWarnTime());
	}
	public function getWarningPoints(){
		return $this->getLoginDatum("warnpts");
	}
	public function getLoginDatum($key, $default = null){
		return isset($this->loginData[$key]) ? $this->loginData[$key] : $default;
	}
	public function getLastWarnTime(){
		return $this->getLoginDatum("lastwarn");
	}
	/**
	 * @return Player
	 */
	public function getPlayer(){
		return $this->player;
	}
	public function __toString(){
		return $this->getPlayer()->getDisplayName();
	}
	public function onJoin(){
		foreach($this->player->getLevel()->getChunkPlayers($this->player->getFloorX() >> 4, $this->player->getFloorZ() >> 4) as $other){
			$other->hidePlayer($this->player);
			$this->invisibleFrom[$other->getId()] = true;
		}
		$this->prepareLogin();
	}
	private function prepareLogin(){
		$status = $this->getLoginDatum("status");
		if($status === Settings::STATUS_TRANSFERRING and $this->getPlayer()->getUniqueId() === $this->getLoginDatum("authuuid")){
			$this->login(self::AUTH_TRANSFER);
			return;
		}
		if($this->getLoginDatum("isnew")){
			$this->state = self::STATE_REGISTERING;
		}else{
			$method = $this->getAuthSettings();
			if(!$this->getLoginDatum("isnew") and $method === Settings::CONFIG_AUTH_UUID and $this->getPlayer()->getUniqueId() === $this->getLoginDatum("authuuid")){
				$this->login(self::AUTH_UUID);
				return;
			}
			if($method === Settings::CONFIG_AUTH_IP_LAST and $this->getPlayer()->getAddress() === $this->getLoginDatum("lastip")){
				$this->login(self::AUTH_IP_LAST);
				return;
			}
			if($method === Settings::CONFIG_AUTH_IP_HISTORY and in_array($this->getPlayer()->getAddress(), $this->getIPHistory())){
				$this->login(self::AUTH_IP_HIST);
				return;
			}
			if($method === Settings::CONFIG_AUTH_SUBNET_LAST and $this->subnet_matches($this->getPlayer()->getAddress(), $this->getLoginDatum("lastip"))){
				$this->login(self::AUTH_SUBNET_LAST);
			}
			// deprecated: subnet hist
			$this->state = self::STATE_LOGIN;
		}
		if($this->isLoggingIn()){
			$this->send(Phrases::LOGIN_PASS_PROMPT);
		}else{
			$this->send(Phrases::LOGIN_REGISTER_PROMPT);
		}
	}
	public function postOnline(){
		$class = Settings::$LOCALIZE_CLASS;
		$ip = Settings::$LOCALIZE_IP;
		$port = Settings::$LOCALIZE_PORT;
		$online = Settings::STATUS_ONLINE;
		new RawAsyncQuery($this->getMain(), "UPDATE users SET lastip='{$this->getPlayer()->getAddress()}',status=$online,laston=unix_timestamp(),lastses=$class,status_ip='$ip',status_port=$port WHERE uid=" . $this->getUid());
	}
	/**
	 * Override this method to do initialization stuff
	 * @param int $method
	 */
	public function login($method){
		$this->state = self::STATE_PLAYING;
		$this->postOnline();
		$this->send(Phrases::LOGIN_AUTH_SUCCESS, ["method" => $this->translate(self::$AUTH_METHODS_PHRASES[$method])]);
		$this->send(Phrases::LOGIN_AUTH_WHEREAMI, [
				"class" => $this->translate(Settings::$CLASSES_NAMES_PHRASES[Settings::$LOCALIZE_CLASS]),
				"ip" => Settings::$LOCALIZE_IP, "port" => (string)Settings::$LOCALIZE_PORT]
		);
		$this->recalculateNametag();
		$this->setMaintainedPopup();
		foreach($this->getMain()->getServer()->getOnlinePlayers() as $other){
			if(isset($this->invisibleFrom[$other->getId()])){
				$other->showPlayer($this->getPlayer());
				unset($this->invisibleFrom[$other->getId()]);
			}
		}
		$this->invisibleFrom = [];
		$att = $this->getPlayer()->addAttachment($this->getMain());
		$att->setPermission("pocketmine.broadcast.admin", false);
		$att->setPermission("pocketmine.broadcast.user", true);
		$att->setPermission("pocketmine.whitelist", false);
		$att->setPermission("pocketmine.command.ban", false);
		$att->setPermission("pocketmine.command.unban", false);
		$att->setPermission("pocketmine.command.op", false);
		$att->setPermission("pocketmine.command.save", false);
		$att->setPermission("pocketmine.command.time", $this->isModerator(false));
		$att->setPermission("pocketmine.command.kill", false);
		$att->setPermission("pocketmine.command.kill.self", true);
		$att->setPermission("pocketmine.command.kill.other", false);
		$att->setPermission("pocketmine.command.me", false);
		$att->setPermission("pocketmine.command.tell", false);
		$att->setPermission("pocketmine.command.say", false);
		$att->setPermission("pocketmine.command.give", $this->isModerator(false));
		$att->setPermission("pocketmine.command.effect", $this->isModerator(false));
		$att->setPermission("pocketmine.command.particle", $this->isAdmin(false));
		$att->setPermission("pocketmine.command.teleport", $this->isModerator(false));
		$att->setPermission("pocketmine.command.kick", $this->isModerator(false));
		$att->setPermission("pocketmine.command.stop", $this->isAdmin(false));
		$att->setPermission("pocketmine.command.list", true);
		$att->setPermission("pocketmine.command.help", true);
		$att->setPermission("pocketmine.command.plugins", false);
		$att->setPermission("pocketmine.command.reload", false);
		$att->setPermission("pocketmine.command.gamemode", $this->isModerator());
		$att->setPermission("pocketmine.command.defaultgamemode", false);
		$att->setPermission("pocketmine.command.seed", false);
		$att->setPermission("pocketmine.command.status", false);
		$att->setPermission("pocketmine.command.gc", $this->isAdmin());
		$att->setPermission("pocketmine.command.timings", $this->isAdmin());
		$att->setPermission("pocketmine.command.spawnpoint", false);
		$att->setPermission("pocketmine.command.setworldspawn", $this->isAdmin());
		$att->setPermission("fasttransfer.command.transfer", $this->isAdmin());
	}
	public function send($phrase, array $vars = []){
		$this->getPlayer()->sendMessage($this->translate($phrase, $vars));
	}
	public function translate($phrase, array $vars = []){
		return $this->getMain()->getLanguageManager()->get($phrase, $vars, ...$this->getLangs());
	}
	/**
	 * @return BasePlugin
	 */
	public abstract function getMain();
	public function getLangs(){
		$array = $this->getLoginDatum("langs");
		if(!in_array("en", $array)){
			$array[] = "en";
		}
		return $array;
	}
	public function recalculateNametag(){
		$this->setInGameName($plain = $this->calculatePlainName());
		$this->getPlayer()->setDisplayName($plain);
		$this->getPlayer()->setNameTag($tag = $this->calculateNameTag());
		$this->getPlayer()->sendTip($this->translate(Phrases::LOGIN_KNOWN_AS, ["tag" => $tag]));
	}
	public function calculatePlainName($nameColor = TextFormat::WHITE){
		$rank = $this->calculateRank();
		if($rank !== ""){
			$tag = Phrases::VAR_symbol . "{" . $rank . Phrases::VAR_symbol . "}";
		}else{
			$tag = "";
		}
		$lbl = $this->getLabelInUse();
		if($lbl !== ""){
			$tag .= Phrases::VAR_symbol . "[" . $lbl . Phrases::VAR_symbol . "]";
		}
		if(!$this->isEmailVerified()){
			$tag .= TextFormat::GRAY . "(UV)";
		}
		$tag .= $nameColor . $this->getPlayer()->getName();
		return $tag;
	}
	public function calculateNameTag($nameColor = TextFormat::WHITE){
		$tag = "";
		$teamname = $this->getTeamName();
		if($teamname){
			$tag .= TextFormat::DARK_AQUA . "Team " . TextFormat::GOLD . $teamname;
			$tag .= TextFormat::GREEN . "(" . $this->getTeamRank() . ")\n";
		}
		$rank = $this->calculateRank();
		if($rank !== ""){
			$tag .= $rank . "\n";
		}
		$lbl = $this->getLabelInUse();
		if($lbl !== ""){
			$tag .= Phrases::VAR_symbol . "[" . $lbl . Phrases::VAR_symbol . "]";
		}
		if(!$this->isEmailVerified()){
			$tag .= TextFormat::GRAY . "(UV)";
		}
		$tag .= $nameColor . $this->getPlayer()->getName();
		return $tag;
	}
	private function calculateRank(){
		$rank = $this->getRank();
		$prefix = TextFormat::AQUA;
		if($rank & 0x1000){
			$prefix .= "Trial ";
		}
		if($rank & 0x2000){
			$prefix .= "Head ";
		}
		if($rank & 0x0800){
			return $prefix . "Dev";
		}
		if($rank & 0x0080){
			return $prefix . "HeadOfStaff";
		}
		if($rank & 0x0040){
			return $prefix . "Owner";
		}
		if($rank & 0x0020){
			return $prefix . "Admin";
		}
		if($rank & 0x0010){
			return $prefix . "Mod";
		}
		if($rank & 0x4000){
			return TextFormat::DARK_AQUA . "YT";
		}
		$suffix = "";
		if($rank & 1){
			$suffix = TextFormat::LIGHT_PURPLE . "+";
		}
		if(($rank & 0x000C) === 0x000C){
			return TextFormat::GOLD . "VIP$suffix";
		}
		if($rank & 0x0004){
			return TextFormat::GOLD . "Donator$suffix";
		}
		return ($suffix === "+") ? "Tester" : "";
	}
	public function getRank(){
		return $this->getLoginDatum("rank");
	}
	/**
	 * @return string
	 */
	public function getLabelInUse(){
		$approved = $this->getLoginDatum("lblappr");
		return $this->canUseLabel($approved) ? $this->getLoginDatum("lbl") : "";
	}
	public function canUseLabel($approved){
		if($approved < Settings::LABEL_APPROVED_EVERYONE and !$this->isModerator()){
			return false;
		}
		if($approved === Settings::LABEL_APPROVED_EVERYONE){
			return true;
		}
		if($approved === Settings::LABEL_APPROVED_DONATOR and ($this->isDonator() or $this->isModerator())){
			return true;
		}
		if($approved === Settings::LABEL_APPROVED_VIP and ($this->isVIP() or $this->isModerator())){
			return true;
		}
		if($approved === Settings::LABEL_APPROVED_MOD and $this->isModerator()){
			return true;
		}
		return $this->isAdmin() ? true : false;
	}
	public function isModerator($includeTrial = true){
		$rank = $this->getRank();
		return ($rank & Settings::RANK_PERM_MOD) === Settings::RANK_PERM_ADMIN and ($includeTrial or ($rank & Settings::RANK_PREC_TRIAL) === 0);
	}
	public function isDonator(){
		return (bool)($this->getRank() & Settings::RANK_IMPORTANCE_DONATOR);
	}
	public function isVIP(){
		return (bool)($this->getRank() & Settings::RANK_IMPORTANCE_VIP);
	}
	public function isAdmin($includeTrial = true){
		$rank = $this->getRank();
		return ($rank & Settings::RANK_PERM_ADMIN) === Settings::RANK_PERM_ADMIN and ($includeTrial or ($rank & Settings::RANK_PREC_TRIAL) === 0);
	}
	public function getTeamName(){
		return $this->getLoginDatum("teamname");
	}
	public function getTeamRank(){
		return $this->getLoginDatum("teamrank");
	}
	public function setMaintainedPopup($popup = null){
		if($this->curPopup === $popup){
			return;
		}
		$this->curPopup = $popup;
		if($popup !== null){
			$this->getPlayer()->sendPopup($popup);
		}else{
			$this->getPlayer()->sendPopup(" ");
		}
	}
	public function getAuthSettings(){
		return $this->getLoginDatum("config") & Settings::CONFIG_SECTOR_AUTH;
	}
	public function getIPHistory(){
		return array_filter(explode(",", $this->getLoginDatum("iphist")));
	}
	private function subnet_matches($ip0, $ip1){
		if($ip0 === $ip1){
			return true;
		}
		$ip0 = explode(".", $ip0);
		$ip1 = explode(".", $ip1);
		if(count($ip0) !== 4 or count($ip1) !== 4){
			return false;
		}
		return $ip0[0] === $ip1[0] and $ip0[1] = $ip1[1];
	}
	public function isLoggingIn(){
		return ($this->state & 0xF0) === self::STATE_LOGIN;
	}
	public function isSpammer(){
		return $this->getRank() & Settings::RANK_PERM_SPAM;
	}
	public function isBuilder(){
		return ($this->getRank() & Settings::RANK_PERM_WORLD_EDIT) === Settings::RANK_PERM_WORLD_EDIT;
//		return $this->getRank() & Settings::RANK_PERM_WORLD_EDIT;
	}
	public function isDeveloper(){
		return $this->getRank() & Settings::RANK_PERM_DEV;
	}
	public function onCmd(PlayerCommandPreprocessEvent $event){
		if($this->isRegistering()){
			$event->setCancelled();
			$len = strlen($event->getMessage());
			$event->setMessage($hash = self::hash($event->getMessage(), $this->getUid()));
			if($this->state === self::STATE_REGISTERING_FIRST){
				$this->tmpHash = $hash;
				$this->sendCurlyLines();
				$this->send(Phrases::LOGIN_REGISTER_RETYPE);
				$this->state = self::STATE_REGISTERING_SECOND;
			}elseif($this->state === self::STATE_REGISTERING_SECOND){
				$this->sendCurlyLines();
				if($this->tmpHash === $hash){
					$this->sendCurlyLines();
					$this->setLoginDatum("hash", $hash);
					$this->setLoginDatum("pwprefix", "~");
					$this->setLoginDatum("pwlen", $len);
					$this->state = self::STATE_PLAYING;
					$this->sendFirstJoinMessages();
					$this->login(self::AUTH_REG);
					$this->send(Phrases::LOGIN_REGISTER_SUCCESS);
				}else{
					$this->send(Phrases::LOGIN_REGISTER_MISMATCH);
					$this->tmpHash = null;
					$this->state = self::STATE_REGISTERING_FIRST;
				}
			}
			return false;
		}elseif($this->isLoggingIn()){
			$event->setMessage($hash = self::hash($event->getMessage(), $this->getUid()));
			$this->sendCurlyLines();
			if($hash === $this->getPasswordHash()){
				$this->login(self::AUTH_PASS);
			}else{
				$this->state++;
				$chances = "chance";
				MUtils::word_quantitize($chances, 5 - $this->getStatePrecise());
				$this->send(Phrases::LOGIN_PASS_MISMATCH, ["chances" => $chances]);
				if($this->getStatePrecise() === 5){
					$this->getPlayer()->kick("Failure to login within 5 attempts");
					return false;
				}
			}
			return false;
		}else{
			$msg = $event->getMessage();
			$len = $this->getLoginDatum("pwlen");
			$msgLen = strlen($msg);
			for($i = 0; $i < $msgLen; $i++){
				$substr = substr($msg, $i, $len);
				if(strlen($substr) < $len){
					break;
				}
				if($this->getPasswordHash() === $this->hash($substr, $this->getUid())){
					$this->send(Phrases::CHAT_BLOCKED_PASS);
					return false;
				}
			}
			$firstChar = substr($event->getMessage(), 0, 1);
			if($firstChar === "/"){
				return true;
			}elseif($firstChar === "\\"){
				$event->setMessage("/" . substr($event->getMessage(), 1));
			}
			$isLocal = $firstChar !== ".";
			if(!$isLocal){
				$event->setMessage(substr($event->getMessage(), 1));
			}
			$message = trim($event->getMessage());
			if(!$this->spamDetector->censor($message)){
				return false;
			}
			if($this->currentChatState === self::CHANNEL_TEAM){
				$data = [
					"tid" => $this->getTeamId(),
					"teamName" => $this->getTeamName(),
					"ign" => $this->getInGameName()
				];
				$type = new TeamChatType($this->getMain(), $this->getPlayer()->getDisplayName(), $message, $isLocal ? Settings::$LOCALIZE_CLASS : Settings::CLASS_ALL, $data);
				$type->push();
				return false;
			}
			if($this->currentChatState !== self::CHANNEL_LOCAL){
				$data = [
					"channel" => $this->currentChatState,
					"fromClass" => Settings::$LOCALIZE_CLASS,
					"ign" => $this->getInGameName()
				];
				$type = new ChannelChatType($this->getMain(), $this->getPlayer()->getDisplayName(), $message, $isLocal ? Settings::$LOCALIZE_CLASS : Settings::CLASS_ALL, $data);
				$type->push();
				return false;
			}
			$this->onChat($message, $isLocal ? self::CHAT_LOCAL : self::CHAT_STD);
			return false;
		}
	}
	public function isRegistering(){
		return ($this->state & 0xF0) === self::STATE_REGISTERING;
	}
	public static function hash($password, $uid){
		return bin2hex(hash("sha512", $password . $uid, true) ^ hash("whirlpool", $uid . $password, true));
	}
	public function getUid(){
		return $this->getLoginDatum("uid");
	}
	public function sendCurlyLines($lines = 1, $color = TextFormat::ITALIC . TextFormat::RED){
		for($i = 0; $i < $lines; $i++){
			$this->getPlayer()->sendMessage($color . str_repeat("~", 40));
		}
	}
	public function setLoginDatum($key, $datum){
		$this->loginData[$key] = $datum;
	}
	private function sendFirstJoinMessages(){
		$this->getMain()->sendFirstJoinMessages($this->getPlayer());
	}
	public function getPasswordHash(){
		return $this->getLoginDatum("hash");
	}
	public function getStatePrecise(){
		return $this->state & 0x0F;
	}
	public function getTeamId(){
		return $this->getLoginDatum("tid");
	}
	public function getInGameName(){
		return $this->inGameName;
	}
	public function setInGameName($ign){
		$this->inGameName = $ign;
	}
	/**
	 * @param string $msg
	 * @param int $type
	 */
	public function onChat($msg, $type){
		$msg = TextFormat::clean($msg);
		/** @var MuteIssue $mute */
		if($this->getMain()->isMuted($this, $mute)){
			$this->send(Phrases::WARNING_MUTED_NOTIFICATION, [
				"length" => MUtils::time_secsToString($mute->length),
				"since" => date($this->translate("date.format"), $mute->since),
				"till" => date($this->translate("date.format"), $mute->since + $mute->length),
				"passed" => MUtils::time_secsToString(time() - $mute->since),
				"left" => MUtils::time_secsToString($mute->since + $mute->length - time()),
			]);
			return;
		}
		$msg = $this->getChatColor() . preg_replace_callback('/@([A-Za-z_]{2,16})/', function ($match){
				if(($player = $this->getMain()->getServer()->getPlayer($match[1])) !== null){
					return TextFormat::DARK_AQUA . TextFormat::ITALIC . $player->getName() . TextFormat::RESET . $this->getChatColor();
				}
				return TextFormat::ITALIC . TextFormat::GRAY . $match[0] . TextFormat::RESET . $this->getChatColor();
			}, $msg);
		foreach($this->getMain()->getSessions() as $ses){
			// TODO handle $type
			if($ses->isLocalChatOn() and ($type !== self::CHAT_LOCAL or Settings::isLocalChat($ses->getPlayer(), $this->getPlayer()))){
				$ses->getPlayer()->sendMessage($this->getPlayer()->getDisplayName() . ($type === self::CHAT_ME ? ": " : ">") . $this->getChatColor() . $msg);
			}
		}
	}
	public function getChatColor(){
		if($this->isAdmin()){
			return TextFormat::LIGHT_PURPLE . TextFormat::BOLD;
		}
		if($this->isModerator()){
			return TextFormat::LIGHT_PURPLE;
		}
		if($this->isVIP()){
			return TextFormat::WHITE . TextFormat::BOLD;
		}
		if($this->isDonator()){
			return TextFormat::WHITE;
		}
		return TextFormat::GRAY;
	}
	public function isLocalChatOn(){
		return (bool)($this->getLoginDatum("config") & Settings::CONFIG_LOCAL_CHAT_ON) and $this->state === self::STATE_PLAYING;
	}
	public function isOwner(){
		return ($this->getRank() & Settings::RANK_PERM_OWNER) === Settings::RANK_PERM_OWNER;
	}
	public function onDamage(/** @noinspection PhpUnusedParameterInspection */
		EntityDamageEvent $event){
		if(!$this->isPlaying()){
			return false;
		}
		return true;
	}
	public function isPlaying(){
		return ($this->state & 0xF0) === self::STATE_PLAYING;
	}
	public function onDeath(/** @noinspection PhpUnusedParameterInspection */
		PlayerDeathEvent $event){
		return true;
	}
	public function onMove(/** @noinspection PhpUnusedParameterInspection */
		PlayerMoveEvent $event){
		if(!$this->isPlaying()){
			$this->setMaintainedPopup(TextFormat::RED . "Please " . ($this->isRegistering() ? "register" : "login") . " by typing your password directly into chat.");
			$from = $event->getFrom();
			$to = $event->getTo();
			return ($from->x === $to->x) and ($from->y === $to->y) and ($from->z === $to->z);
		}
		return true;
	}
	public function onConsume(/** @noinspection PhpUnusedParameterInspection */
		PlayerItemConsumeEvent $event){
		if(!$this->isPlaying()){
			$this->setMaintainedPopup(TextFormat::RED . "Please " . ($this->isRegistering() ? "register" : "login") . " by typing your password directly into chat.");
			return false;
		}
		return true;
	}
	public function onDropItem(/** @noinspection PhpUnusedParameterInspection */
		PlayerDropItemEvent $event){
		if(!$this->isPlaying()){
			$this->setMaintainedPopup(TextFormat::RED . "Please " . ($this->isRegistering() ? "register" : "login") . " by typing your password directly into chat.");
			return false;
		}
		return true;
	}
	public function onInteract(/** @noinspection PhpUnusedParameterInspection */
		PlayerInteractEvent $event){
		if(!$this->isPlaying()){
			$this->setMaintainedPopup(TextFormat::RED . "Please " . ($this->isRegistering() ? "register" : "login") . " by typing your password directly into chat.");
			return false;
		}
		return true;
	}
	public function onRespawn(/** @noinspection PhpUnusedParameterInspection */
		PlayerRespawnEvent $event){
		if(!$this->isPlaying()){
			$this->setMaintainedPopup(TextFormat::RED . "Please " . ($this->isRegistering() ? "register" : "login") . " by typing your password directly into chat.");
			return false;
		}
		return true;
	}
	public function onBreak(/** @noinspection PhpUnusedParameterInspection */
		BlockBreakEvent $event){
		if(!$this->isPlaying()){
			$this->setMaintainedPopup(TextFormat::RED . "Please " . ($this->isRegistering() ? "register" : "login") . " by typing your password directly into chat.");
			return false;
		}
		return true;
	}
	public function onPlace(/** @noinspection PhpUnusedParameterInspection */
		BlockPlaceEvent $event){
		if(!$this->isPlaying()){
			$this->setMaintainedPopup(TextFormat::RED . "Please " . ($this->isRegistering() ? "register" : "login") . " by typing your password directly into chat.");
			return false;
		}
		return true;
	}
	public function onOpenInv(/** @noinspection PhpUnusedParameterInspection */
		InventoryOpenEvent $event){
		if(!$this->isPlaying()){
			$this->setMaintainedPopup(TextFormat::RED . "Please " . ($this->isRegistering() ? "register" : "login") . " by typing your password directly into chat.");
			return false;
		}
		return true;
	}
	public function onPickupItem(/** @noinspection PhpUnusedParameterInspection */
		InventoryPickupItemEvent $event){
		if(!$this->isPlaying()){
			return false;
		}
		return true;
	}
	public function onPickupArrow(/** @noinspection PhpUnusedParameterInspection */
		InventoryPickupArrowEvent $event){
		if(!$this->isPlaying()){
			return false;
		}
		return true;
	}
	public function onChatEvent(/** @noinspection PhpUnusedParameterInspection */
		PlayerChatEvent $event){
		$msg = $event->getMessage();
		$this->onChat($msg, self::CHAT_STD);
		return false;
	}
	public function onHoldItem(/** @noinspection PhpUnusedParameterInspection */
		PlayerItemHeldEvent $event){
		return true;
	}
	public function onTeleport(/** @noinspection PhpUnusedParameterInspection */
		EntityTeleportEvent $event){
		return true;
	}
	public function onQuit(){
		$this->saveData();
	}
	public function saveData($newStatus = Settings::STATUS_OFFLINE){
		if($this->state === self::STATE_PLAYING){ // don't save if not registered/logged in or transferring
			$this->getMain()->saveSessionData($this, $newStatus);
		}
	}
	public function incrLoginDatum($key, $amplitude = 1){
		$this->loginData[$key] += $amplitude;
		return $this->loginData[$key];
	}
	public function getNicks(){
		return array_filter(explode("|", $this->getLoginDatum("nicks")));
	}
	public function addIp($ip){
		$this->setLoginDatum("iphist", $this->getLoginDatum("iphist") . "$ip,");
		new AddIpQuery($this->getMain(), $ip, $this->getUid());
	}
	public function grantCoins($coins, $ignoreGrind = false, $effects = true, $bonus = true){
		if(!$ignoreGrind and $this->isGrinding()){
			$coins *= Settings::getGrindFactor($this->getRank());
		}
		if($effects){
			$this->getPlayer()->getLevel()->addSound(new FizzSound($this->getPlayer()), [$this->getPlayer()]);
			$random = new Random(time() + $coins);
			$player = $this->getPlayer();
			$particle = new TerrainParticle($player, Block::get(Block::GOLD_BLOCK));
			$level = $player->getLevel();
			$recipients = [$player];
			for($i = 0; $i < 500; $i++){
				$x = $random->nextSignedFloat();
				$y = $random->nextSignedFloat();
				$z = $random->nextSignedFloat();
				$particle->setComponents(
					$player->x + $x,
					$player->y + $y,
					$player->z + $z
				);
				$level->addParticle($particle, $recipients);
			}
		}
		$this->setCoins($out = $this->getCoins() + $coins);
		if($bonus){
			if(mt_rand(0, 99) === 0){
				$add = mt_rand(25, 50);
			}elseif(mt_rand(0, 499) === 0){
				$add = mt_rand(150, 300);
			}elseif(mt_rand(0, 749) === 0){
				$add = mt_rand(300, 500);
			}
			if(isset($add)){
				$this->grantCoins($add, false, true, false);
			}
		}
		return [$coins, $out];
	}
	public function isGrinding(){
		return time() - $this->getLastGrind() <= Settings::getGrindLength($this->getRank());
	}
	public function getLastGrind(){
		return $this->getLoginDatum("lastgrind");
	}
	public function setCoins($coins){
		$this->setLoginDatum("coins", $coins);
	}
	public function getCoins(){
		return $this->getLoginDatum("coins");
	}
	public function getAndUpdateCoinsDelta(){
		$coins = $this->getCoins();
		$delta = $coins - $this->coinsOld;
		$this->coinsOld = $coins;
		return $delta;
	}
	public function getAndUpdateOntime(){
		$now = microtime(true);
		$result = $now - $this->ontimeSince;
		$this->ontimeSince = $now;
		return $result;
	}
	public function getPasswordPrefix(){
		return $this->getLoginDatum("pwprefix");
	}
	public function getPasswordLength(){
		return $this->getLoginDatum("pwlen");
	}
	public function getRegisterTime(){
		return $this->getLoginDatum("registration");
	}
	public function getLastOnline(){
		return $this->getLoginDatum("laston");
	}
	public function getAllSettings(){
		return $this->getLoginDatum("config");
	}
	public function getTagEnabled(){
		return (bool)($this->getLoginDatum("config") & Settings::CONFIG_TAG_ON);
	}
	public function getStatsPublic(){
		return (bool)($this->getLoginDatum("config") & Settings::CONFIG_STATS_PUBLIC);
	}
	public function isTeamChannelOn(){
		return (bool)($this->getLoginDatum("config") & Settings::CONFIG_TEAM_CHANNEL_ON);
	}
	public function canStartGrind(){
		if(!$this->isDonator()){
			return false;
		}
		return time() - $this->getLastGrind() >= Settings::getGrindExpiry($this->getRank());
	}
	public function getGrindWaitTime(){
		return max(0, $this->getLastGrind() + Settings::getGrindExpiry($this->getRank()) - time());
	}
	public function startGrinding(){
		$this->setLoginDatum("lastgrind", time());
	}
	public function isEmailVerified(){
		return substr($this->getLoginDatum("email"), 0, 1) !== "~";
	}
	public function isDonatorPlus(){
		return ($this->getRank() & Settings::RANK_IMPORTANCE_DONATOR_PLUS) === Settings::RANK_IMPORTANCE_DONATOR_PLUS;
	}
	public function isVIPPlus(){
		return ($this->getRank() & Settings::RANK_IMPORTANCE_VIP_PLUS) === Settings::RANK_IMPORTANCE_VIP_PLUS;
	}
	public function addWarningPoints($pts){
		$this->setLoginDatum("warnpts", $this->getWarningPoints() + $pts);
		$this->setLoginDatum("lastwarn", time());
	}
	public function warn($id, $points, CommandSender $issuer, $msg){
		/** @noinspection PhpDeprecationInspection */
		new PreExecuteWarningQuery($this->getMain(), $this->getUid(), $this->getPlayer()->getClientId(), $id, $points, $issuer, $msg);
	}
	public function getTeamJoinTime(){
		return $this->getLoginDatum("teamjoin");
	}
	public function getIgnoreList(){
		return array_filter(explode(",", $this->getLoginDatum("ignorelist")));
	}
	public function ignore($name){
		if(!$this->isIgnoring($name)){
			$this->setLoginDatum("ignorelist", $this->getLoginDatum("ignorelist") . strtolower($name) . ",");
			return true;
		}
		return false;
	}
	public function isIgnoring($name, &$pos = 0){
		return ($pos = strpos($name, "," . strtolower($name) . ",")) !== false;
	}
	public function unignore($name){
		if($this->isIgnoring($name, $pos)){
			$list = $this->getLoginDatum("ignorelist");
			$this->setLoginDatum("ignorelist", substr($list, $pos, strlen($name) + 1));
			return true;
		}
		return false;
	}
	public function isNew(){
		return isset($this->loginData["isnew"]) and $this->loginData["isnew"] === true;
	}
	public function isOnChannel($channel){
		return isset(array_change_key_case($this->getLoginDatum("channels"), CASE_LOWER)[strtolower($channel)]);
	}
	public function joinChannel($channel, $subLevel = self::CHANNEL_SUB_NORMAL){
		$subs = $this->getChannelSubscriptions();
		if(isset(array_change_key_case($subs, CASE_LOWER)[strtolower($channel)])){
			return false;
		}
		$subs[$channel] = $subLevel;
		$this->setLoginDatum("channels", $subs);
		new JoinChannelQuery($this->getMain(), $this->getUid(), $channel, $subLevel);
		return true;
	}
        
	public function getChannelSubscriptions(){
		return $this->getLoginDatum("channels");
	}
	public function getCurrentChatState() {
                return $this->currentChatState;
        }
	public function partChannel($channel){
		$subs = $this->getChannelSubscriptions();
		$lowerChans = array_keys(array_change_key_case($subs, CASE_LOWER));
		$pos = array_search(strtolower($channel), $lowerChans);
		if($pos !== false){
			$caseName = array_keys($subs)[$pos];
			unset($subs[$caseName]);
			$this->setLoginDatum("channels", $subs);
			new PartChannelQuery($this->getMain(), $this->getUid(), $caseName);
		}
	}
	public function getFriends($level = self::FRIEND_LEVEL_GOOD_FRIEND){
		$out = [];
		foreach($this->getLoginDatum("friends") as $uid => $type){
			if(($type & $level) === $level){
				$out[] = $uid;
			}
		}
		return $out;
	}
	public function inviteIncrease($uid, $targetName, &$vars){
		$vars = ["target" => $targetName];
		$smallUid = min($uid, $this->getUid());
		$largeUid = max($uid, $this->getUid());
		$currentType = $this->getFriendType($uid, $io, $toLarge, $all);
		if($io === self::FRIEND_OUT){
			return Phrases::CMD_FRIEND_ALREADY_INVITED;
		}
		if($io === self::FRIEND_IN){
			$new = $currentType << 1;
			$all[$uid] = $new;
			$this->setLoginDatum("friends", $all);
			new RawAsyncQuery($this->getMain(), "UPDATE friends SET type=$new WHERE smalluid=$smallUid AND largeuid=$largeUid");
			$vars["newtype"] = $this->translate(self::$FRIEND_TYPES[$new]);
			return Phrases::CMD_FRIEND_RAISED;
		}
		if($currentType === self::FRIEND_LEVEL_MAX){
			return Phrases::CMD_FRIEND_MAX;
		}
		$new = $currentType & ($toLarge ? self::FRIEND_REQUEST_TO_LARGE : self::FRIEND_REQUEST_TO_SMALL);
		$all[$uid] = $new;
		$this->setLoginDatum("friends", $all);
		new RawAsyncQuery($this->getMain(), $currentType === self::FRIEND_LEVEL_NONE ? "INSERT INTO friends (smalluid, largeuid, type) VALUES ($smallUid, $largeUid, $new)" : "UPDATE friends SET type=$new WHERE smalluid=$smallUid AND largeuid=$largeUid");
		$vars["newtype"] = $this->translate(self::$FRIEND_TYPES[$currentType << 1]);
		return Phrases::CMD_FRIEND_RAISE_REQUESTED;
	}
	public function getFriendType($uid, &$io = 0, &$toLarge = false, &$all = []){
		$all = $this->getLoginDatum("friends");
		$type = isset($all[$uid]) ? $all[$uid] : 0;
		$toLarge = $uid > $this->getUid();
		$req = $type & self::FRIEND_BITMASK_REQUEST;
		if($req === self::FRIEND_REQUEST_TO_LARGE and $toLarge or $req === self::FRIEND_REQUEST_TO_SMALL and !$toLarge){
			$io = self::FRIEND_OUT;
		}elseif($req === 0){
			$io = self::FRIEND_NO_REQUEST;
		}else{
			$io = self::FRIEND_IN;
		}
		return $type & ~self::FRIEND_BITMASK_REQUEST;
	}
	public function reduceFriend($uid){
		$smallUid = min($this->getUid(), $uid);
		$largeUid = max($this->getUid(), $uid);
		$type = $this->getFriendType($uid, $io, $toLarge, $all);
		if($type === self::FRIEND_LEVEL_NONE){
			return false;
		}
		$new = $type >> 1;
		if($new === 0){
			unset($all[$uid]);
		}else{
			$all[$uid] = $new;
		}
		$this->setLoginDatum("friends", $all);
		new RawAsyncQuery($this->getMain(), $new === self::FRIEND_LEVEL_NONE ? "DELETE FROM friends WHERE smalluid=$smallUid AND largeuid=$largeUid" : "UPDATE friends SET type=$new WHERE smalluid=$smallUid AND largeuid=$largeUid");
		return true;
	}
	public function rejectFriend($uid){
		$smallUid = min($this->getUid(), $uid);
		$largeUid = max($this->getUid(), $uid);
		$type = $this->getFriendType($uid, $originalIo, $toLarge, $all);
		$all[$uid] = $type;
		$this->setLoginDatum("friends", $all);
		new RawAsyncQuery($this->getMain(), $type === self::FRIEND_LEVEL_NONE ? "DELETE FROM friends WHERE smalluid=$smallUid AND largeuid=$largeUid" : "UPDATE friends SET type=$type WHERE smalluid=$smallUid AND largeuid=$largeUid");
		return $originalIo;
	}
	public function getPurchases(){
		return $this->getLoginDatum("purchases", []);
	}
	public function getPurchase($pid){
		$purchases = $this->getLoginDatum("purchases");
		return isset($purchases[$pid]) ? $purchases[$pid] : null;
	}
	/**
	 * @return SpamDetector
	 */
	public function getSpamDetector(){
		return $this->spamDetector;
	}
	public function getPopup(){
		return $this->curPopup;
	}
	public function setState($state){
		$this->state = $state;
	}
	public function getCurrentFaceSkin(){
		$seeks = $this->getMain()->getFacePixels();
		$output = "";
		$skin = $this->getPlayer()->getSkinData();
		foreach($seeks as $seek){
			$output .= substr($skin, $seek / 2, 4);
		}
		return $output;
	}
	public function halfSecondTick(){
		if($this->curPopup !== null){
			$this->getPlayer()->sendPopup($this->curPopup);
		}
		if(time() - $this->joinTime > Settings::KICK_PLAYER_TOO_LONG_ONLINE){
			$this->getPlayer()->kick($this->translate(Phrases::KICK_TOO_LONG_ONLINE));
		}
		if($this->isLoggingIn() and time() - $this->joinTime > Settings::KICK_PLAYER_TOO_LONG_LOGIN){
			$this->getPlayer()->kick($this->translate(Phrases::KICK_TOO_LONG_LOGIN));
		}
		$this->postOnlineTimeout--;
		if($this->postOnlineTimeout === 0){
			$this->postOnline();
			$this->postOnlineTimeout = Settings::POST_ONLINE_FREQUENCY;
		}
	}
	public function mute($msg, $length, $src){
		$mute = new MuteIssue;
		/** @noinspection PhpDeprecationInspection */
		$mute->cid = $this->getPlayer()->getClientId();
		$mute->ip = $this->getPlayer()->getAddress();
		$mute->uid = $this->getUid();
		$mute->length = $length;
		$mute->msg = $msg;
		$mute->since = time();
		$mute->src = $src;
		$this->getMain()->addMute($mute);
		$type = MuteChatType::fromObject($this->getMain(), $mute);
		$type->push();
		return $mute;
	}
}
