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
#define $this->getLoginDatum(key) (isset($this->loginData[key]) ? $this->loginData[key] : null)

namespace legionpe\theta;

use legionpe\theta\chat\Hormone;
use legionpe\theta\chat\MuteHormone;
use legionpe\theta\chat\SpamDetector;
use legionpe\theta\config\Settings;
use legionpe\theta\lang\Phrases;
use legionpe\theta\query\AddIpQuery;
use legionpe\theta\query\JoinChannelQuery;
use legionpe\theta\query\PartChannelQuery;
use legionpe\theta\query\PreExecuteWarningQuery;
use legionpe\theta\query\RawAsyncQuery;
use legionpe\theta\query\UpdateHashesQuery;
use legionpe\theta\utils\MUtils;
use legionpe\theta\miscellaneous\walkingparticle\WalkingParticle;
use pocketmine\block\Block;
use pocketmine\command\CommandSender;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\inventory\InventoryPickupArrowEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
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
use pocketmine\inventory\ChestInventory;
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
	const STATE_LOADING = 0x00;
	const STATE_REGISTERING = 0x10;
	const STATE_REGISTERING_FIRST = self::STATE_REGISTERING;
	const STATE_REGISTERING_SECOND = self::STATE_REGISTERING | 0x01;
	const STATE_LOGIN = 0x20;
	const STATE_PLAYING = 0x40;
	const STATE_TRANSFERRING = 0x80;
	const CHAT_NORMAL_LOCAL = 0;
	const CHAT_NORMAL_CLASS = 1;
	const CHAT_ME_LOCAL = 2;
	const CHAT_ME_CLASS = 3;
	const CHANNEL_LOCAL = "&local";
	const CHANNEL_TEAM = "&team";
	const CHANNEL_SUB_VERBOSE = 0;
	const CHANNEL_SUB_NORMAL = 1;
	const CHANNEL_SUB_MENTION = 2;
	public static $TEAM_RANKS = [
		"Junior-Member",
		"Member",
		"Senior-Member",
		"Co-Leader",
		"Leader"
	];
	public $currentChatState = self::CHANNEL_LOCAL;
	/** @var Player */
	private $player;
	/** @var null|WalkingParticle */
	private $walkingParticle = null;
	/** @var mixed[] */
	private $loginData;
	/** @var string */
	private $inGameName = "";
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
	public $confirmGrind = false, $confirmQuitTeam = false, $confirmAlt = false;
	/** @var int|null */
	public $queryTarget = null;
	private $invisibleFrom = [];
	/** @var string|TextContainer|null */
	private $tmpHash = null, $curPopup = null;
	/** @var int half seconds until #postOnline */
	private $postOnlineTimeout = Settings::POST_ONLINE_FREQUENCY;
	public $doHashSaves = false;

	public function __construct(Player $player, $loginData){
		$this->player = $player;
		$this->loginData = $loginData;
		$this->joinTime = time();
		$this->coinsOld = $loginData["coins"];
		$this->ontimeSince = microtime(true);
		if($this->init() === false){
			throw new \Exception;
		}
		$this->recalculateNameTag();
	}
	protected function init(){
		$consequence = $this->getEffectiveConsequence();
		if($consequence->banLength > 0){
			$left = MUtils::time_secsToString($consequence->banLength);
			$this->getPlayer()->kick(TextFormat::RED . "You are banned. You have accumulated " . TextFormat::DARK_PURPLE . $this->getWarningPoints() . TextFormat::RED . " warning points, and you still have " . TextFormat::BLUE . $left . TextFormat::RED . " before you are unbanned. " . TextFormat::AQUA . "Believe this to be a mistake? Email us at " . TextFormat::DARK_PURPLE . "support@legionpvp.eu" . TextFormat::AQUA . " or visit our chatroom at " . TextFormat::DARK_PURPLE . "http://lgpe.co/chat");
			return false;
		}
		$this->spamDetector = new SpamDetector($this);
		return true;
	}
	public function getEffectiveConsequence(){
		return Settings::getWarnPtsConsequence($this->getWarningPoints(), $this->getLastWarnTime());
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
		//foreach($this->player->getLevel()->getChunkPlayers($this->player->getFloorX() >> 4, $this->player->getFloorZ() >> 4) as $other){
		foreach($this->player->getLevel()->getPlayers() as $other){
//			$other->hidePlayer($this->player);
			$this->invisibleFrom[$other->getId()] = true;
		}
		$this->prepareLogin();
	}
	private function prepareLogin(){
		$status = $this->getLoginDatum("status");
		if($status === Settings::STATUS_TRANSFERRING and $this->getPlayer()->getRawUniqueId() === $this->getLoginDatum("authuuid") and (time() - $this->getLastOnline() < 30)){
			$this->login(self::AUTH_TRANSFER);
			return;
		}
		if($this->getLoginDatum("isnew")){
			$this->state = self::STATE_REGISTERING;
		}else{
			$method = $this->getAuthSettings();
			if($method === Settings::CONFIG_AUTH_UUID and $this->getPlayer()->getRawUniqueId() === $this->getLoginDatum("authuuid") and $this->getPlayer()->getAddress() === $this->getLoginDatum("lastip")){
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
		}elseif($this->isRegistering()){
			$this->send(Phrases::LOGIN_REGISTER_PROMPT, ["name" => $this->getPlayer()->getName()]);
		}
	}
	public function postOnline(){
//		$class = Settings::$LOCALIZE_CLASS;
//		$ip = Settings::$LOCALIZE_IP;
//		$port = Settings::$LOCALIZE_PORT;
//		$online = Settings::STATUS_ONLINE;
//		new RawAsyncQuery($this->getMain(), "UPDATE users SET lastip='{$this->getPlayer()->getAddress()}',status=$online,laston=unix_timestamp(),lastses=$class,status_ip='$ip',status_port=$port WHERE uid=" . $this->getUid());
		$this->saveData(Settings::STATUS_ONLINE);
	}
	/**
	 * Override this method to do initialization stuff
	 * @param int $method
	 */
	public function login($method){
		$this->state = self::STATE_PLAYING;
		$this->postOnline();
		if(strpos($this->getLoginDatum("iphist"), "," . $this->getPlayer()->getAddress() . ",") === false){
			$this->addIp($this->getPlayer()->getAddress());
		}
		$this->send(Phrases::LOGIN_AUTH_SUCCESS, ["method" => $this->translate(self::$AUTH_METHODS_PHRASES[$method])]);
		$this->send(Phrases::LOGIN_AUTH_WHEREAMI, [
				"class" => $this->translate(Settings::$CLASSES_NAMES_PHRASES[Settings::$LOCALIZE_CLASS]),
				"ip" => Settings::$LOCALIZE_IP, "port" => (string) Settings::$LOCALIZE_PORT]
		);
		$this->recalculateNameTag();
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
		$att->setPermission("pocketmine.command.ban.player", false);
		$att->setPermission("pocketmine.command.ban.ip", $this->isAdmin());
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
		$this->send(Phrases::LOGIN_AUTH_NOTICE);
	}
	public function send($phrase, array $vars = []){
		if($this->getPlayer()->isOnline()){
			$this->getPlayer()->sendMessage($this->translate($phrase, $vars));
		}
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
	public function isPlaying(){
		return ($this->state & 0xF0) === self::STATE_PLAYING;
	}
	public function isRegistering(){
		return ($this->state & 0xF0) === self::STATE_REGISTERING;
	}
	public function isLoggingIn(){
		return ($this->state & 0xF0) === self::STATE_LOGIN;
	}
	public function standardHandler(){
		if($this->isRegistering()){
			$this->setMaintainedPopup($this->translate(Phrases::LOGIN_POPUP_REGISTER));
			return false;
		}
		if($this->isLoggingIn()){
			$this->setMaintainedPopup($this->translate(Phrases::LOGIN_POPUP_LOGIN));
			return false;
		}
		return $this->isPlaying();
	}

	public function recalculateNameTag(){
		$this->setInGameName($plain = $this->calculatePlainName());
		$this->getPlayer()->setDisplayName($plain);
		$this->getPlayer()->setNameTag($tag = $this->calculateNameTag());
		$this->getPlayer()->sendTip($this->translate(Phrases::LOGIN_KNOWN_AS, ["tag" => $tag]));
	}
	public function calculatePlainName($nameColor = TextFormat::WHITE){
		$tag = "";
		if(!$this->isPlaying()){
			$tag .= TextFormat::DARK_RED . "{UA}";
		}
		$rank = $this->calculateRank();
		if($rank !== ""){
			$tag .= Phrases::VAR_symbol . "{" . $rank . Phrases::VAR_symbol . "}";
		}
		$lbl = $this->getLabelInUse();
		if($lbl !== ""){
			$tag .= Phrases::VAR_symbol . "[" . $lbl . Phrases::VAR_symbol . "]";
		}
		if(!$this->isEmailVerified()){
//			$tag .= TextFormat::GRAY . "(UV)";
		}
		$tag .= $nameColor . $this->getPlayer()->getName();
		return $tag;
	}
	public function calculateNameTag($nameColor = TextFormat::WHITE){
		$tag = "";
		if(!$this->isPlaying()){
			$tag .= TextFormat::DARK_RED . "{Unauthenticated}\n";
		}
		$teamname = $this->getTeamName();
		if($teamname){
			$tag .= TextFormat::DARK_AQUA . "Team " . TextFormat::GOLD . $teamname;
			$tag .= TextFormat::GREEN . "(" . self::$TEAM_RANKS[$this->getTeamRank()] . ")\n";
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
		if($rank & 0x0080){
			return $prefix . "HeadOfStaff";
		}
		if($rank & 0x0040){
			return $prefix . "Owner";
		}
		if($rank & 0x0800){
			return $prefix . "Dev";
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
			$suffix = TextFormat::WHITE . "+";
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
		return ($rank & Settings::RANK_PERM_MOD) === Settings::RANK_PERM_MOD and ($includeTrial or ($rank & Settings::RANK_PRECISION_TRIAL) === 0);
	}
	public function isDonator(){
		return (bool) ($this->getRank() & Settings::RANK_IMPORTANCE_DONATOR);
	}
	public function isVIP(){
		return (bool) ($this->getRank() & Settings::RANK_IMPORTANCE_VIP);
	}
	public function isAdmin($includeTrial = true){
		$rank = $this->getRank();
		return ($rank & Settings::RANK_PERM_ADMIN) === Settings::RANK_PERM_ADMIN and ($includeTrial or ($rank & Settings::RANK_PRECISION_TRIAL) === 0);
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
	public function setWalkingParticle(WalkingParticle $walkingparticle){
		if($this->walkingParticle instanceof WalkingParticle){
			unset($this->getMain()->walkingParticles[$walkingparticle->getId()]);
		}
		$this->walkingParticle = $walkingparticle;
	}
	private function sendFirstJoinMessages(){
		$this->getMain()->sendFirstJoinMessages($this->getPlayer());
	}
	public function getPasswordHash(){
		return $this->getLoginDatum("hash");
	}
	public function isPortingOldPassword(){
		return (trim(strtolower($this->getLoginDatum("hash")), "0f") === "") and (strlen($this->getLoginDatum("oldhash")) === 128) and !$this->isNew();
	}
	public function getPasswordOldHash(){
		return $this->getLoginDatum("oldhash");
	}
	public function getStatePrecise(){
		return $this->state & 0x0F;
	}
	public function getState(){
		return $this->state;
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
			$mute->sendToSession($this);
			return;
		}
		$msg = $this->getChatColor() . preg_replace_callback('/@([A-Za-z0-9_]{1,})/', function ($match){
				if(($session = $this->getMain()->getSession($match[1])) !== null){
					return TextFormat::DARK_AQUA . TextFormat::ITALIC . $session->getInGameName() . TextFormat::RESET . $this->getChatColor();
				}
				return TextFormat::ITALIC . TextFormat::GRAY . $match[0] . TextFormat::RESET . $this->getChatColor();
			}, $msg);
		switch($type){
			case self::CHAT_ME_CLASS:
				$symbol = ": ";
				$local = false;
				break;
			case self::CHAT_NORMAL_CLASS:
				$symbol = ">";
				$local = false;
				break;
			case self::CHAT_ME_LOCAL:
				$symbol = ": ";
				$local = true;
				break;
			case self::CHAT_NORMAL_LOCAL:
				$symbol = ">";
				$local = true;
				break;
			default:
				$symbol = "";
				$local = true;
				break;
		}
		$type = Hormone::get($this->getMain(), Hormone::CLASS_CHAT, $this->getInGameName(), $msg, Settings::$LOCALIZE_CLASS, [
			"ip" => Settings::$LOCALIZE_IP,
			"port" => Settings::$LOCALIZE_PORT,
			"symbol" => $symbol,
			"local" => $local
		]);
		$type->release();
		if($local){
			foreach($this->getMain()->getSessions() as $ses){
				if($ses->isLocalChatOn()){
					$ses->getPlayer()->sendMessage($this->chatPrefix() . $this->getPlayer()->getDisplayName() . $symbol . $this->getChatColor() . $msg);
				}
			}
		}
	}
	protected function chatPrefix(){
		return "";
	}
	public function getChatColor(){
		if($this->isAdmin()){
			return TextFormat::LIGHT_PURPLE . TextFormat::ITALIC;
		}
		if($this->isModerator()){
			return TextFormat::LIGHT_PURPLE;
		}
		if($this->isVIP()){
			return TextFormat::WHITE . TextFormat::ITALIC;
		}
		if($this->isDonator()){
			return TextFormat::WHITE;
		}
		return TextFormat::GRAY;
	}
	public function isLocalChatOn(){
		return (bool) ($this->getLoginDatum("config") & Settings::CONFIG_LOCAL_CHAT_ON) and $this->state === self::STATE_PLAYING;
	}
	public function isClassChatOn(){
		return (bool) ($this->getLoginDatum("config") & Settings::CONFIG_CLASS_CHAT_ON) and $this->state === self::STATE_PLAYING;
	}
	public function isOwner(){
		return ($this->getRank() & Settings::RANK_PERM_OWNER) === Settings::RANK_PERM_OWNER;
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
					$this->saveData(Settings::STATUS_ONLINE);
				}else{
					$this->send(Phrases::LOGIN_REGISTER_MISMATCH);
					$this->tmpHash = null;
					$this->state = self::STATE_REGISTERING_FIRST;
				}
			}
			return false;
		}elseif($this->isLoggingIn()){
			$msg = $event->getMessage();
			$hash = self::hash($msg, $this->getUid());
			$oldHash = self::oldHash($msg);
			$len = strlen($msg);
			$event->setMessage($hash);
			$this->sendCurlyLines();
			if($hash === $this->getPasswordHash()){
				$this->login(self::AUTH_PASS);
			}elseif($this->isPortingOldPassword() and $oldHash === $this->getPasswordOldHash()){
				$this->setLoginDatum("pwprefix", "~");
				$this->setLoginDatum("pwlen", $len);
				$this->setLoginDatum("hash", $hash);
				$this->setLoginDatum("oldhash", "");
				$this->login(self::AUTH_PASS);
				new UpdateHashesQuery($this->getMain(), $this->getUid(), $hash);
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
			$event->setMessage(TextFormat::clean($event->getMessage()));
			$msg = $event->getMessage();
			if(self::hash($msg, $this->getUid()) === $this->getPasswordHash()){
				$this->send(Phrases::CHAT_BLOCKED_PASS);
				return false;
			}
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
			if($firstChar === "\\"){
				$event->setMessage("/" . substr($event->getMessage(), 1));
			}
			if($firstChar === "/"){
				$msg = $event->getMessage();
				if(strpos($msg, " ") === false){
					$cmd = $msg;
					$postCmd = "";
				}else{
					$cmd = strtolower(strstr($msg, " ", true));
					$postCmd = strstr($msg, " ");
				}
				if($cmd === "/w"){
					$cmd = "/tell";
				}
				$event->setMessage($cmd . $postCmd);
				return true;
			}
			$target = $this->getQueryTarget();
			if($target !== null){
				fwrite($this->getMain()->pmLog, "|from:{$this->getPlayer()->getName()}|to:{$target->getPlayer()->getName()}|msg:$msg|" . PHP_EOL);
				$arrows = Phrases::VAR_info . "[" . $this->getPlayer()->getName() . " > " . $target->getPlayer()->getName() . "] " . Phrases::VAR_info . $msg;
				$target->getPlayer()->sendMessage($arrows);
				$this->getPlayer()->sendMessage($arrows);
				return false;
			}
			$this->setQueryTargetUid(null);
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
				$type = Hormone::get($this->getMain(), Hormone::TEAM_CHAT, $this->getPlayer()->getDisplayName(), $message, $isLocal ? Settings::$LOCALIZE_CLASS : Settings::CLASS_ALL, $data);
				$type->release();
				return false;
			}
			if($this->currentChatState !== self::CHANNEL_LOCAL){
				$data = [
					"channel" => $this->currentChatState,
					"fromClass" => Settings::$LOCALIZE_CLASS,
					"ign" => $this->getInGameName()
				];
				$type = Hormone::get($this->getMain(), Hormone::CHANNEL_CHAT, $this->getPlayer()->getDisplayName(), $message, $isLocal ? Settings::$LOCALIZE_CLASS : Settings::CLASS_ALL, $data);
				$type->release();
				return false;
			}
			$this->onChat($message, $isLocal ? self::CHAT_NORMAL_LOCAL : self::CHAT_NORMAL_CLASS);
			return false;
		}
	}
	public function onDamage(/** @noinspection PhpUnusedParameterInspection */
		EntityDamageEvent $event){
		if(!$this->isPlaying()){
			return false;
		}
		if($event instanceof EntityDamageByEntityEvent){
			$player = $event->getDamager();
			if($player instanceof Player){
				$ses = $this->getMain()->getSession($player);
				if($ses instanceof Session){
					if(!$ses->isPlaying()){
						return false;
					}
				}
			}
		}
		return true;
	}
	public function onHeal(/** @noinspection PhpUnusedParameterInspection */
		EntityRegainHealthEvent $event){
		if(!$this->isPlaying()){
			return false;
		}
		return true;
	}
	public function onDeath(/** @noinspection PhpUnusedParameterInspection */
		PlayerDeathEvent $event){
		return true;
	}
	public function onMove(/** @noinspection PhpUnusedParameterInspection */
		PlayerMoveEvent $event){
		return $this->standardHandler();
	}
	public function onConsume(/** @noinspection PhpUnusedParameterInspection */
		PlayerItemConsumeEvent $event){
		return $this->standardHandler();
	}
	public function onDropItem(/** @noinspection PhpUnusedParameterInspection */
		PlayerDropItemEvent $event){
		return $this->standardHandler();
	}
	public function onInteract(/** @noinspection PhpUnusedParameterInspection */
		PlayerInteractEvent $event){
		return $this->standardHandler();
	}
	public function onRespawn(/** @noinspection PhpUnusedParameterInspection */
		PlayerRespawnEvent $event){
		return $this->standardHandler();
	}
	public function onBreak(/** @noinspection PhpUnusedParameterInspection */
		BlockBreakEvent $event){
		return $this->standardHandler();
	}
	public function onPlace(/** @noinspection PhpUnusedParameterInspection */
		BlockPlaceEvent $event){
		return $this->standardHandler();
	}
	public function onOpenInv(/** @noinspection PhpUnusedParameterInspection */
		InventoryOpenEvent $event){
		return $this->standardHandler();
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
	public function onTransaction(InventoryTransactionEvent $event){
		if(!$this->isPlaying()){
			/** @var ChestInventory|null $chest */
			$chest = null;
			foreach($event->getTransaction()->getInventories() as $inv){
				if($inv instanceof ChestInventory){
					$chest = $inv;
				}
			}
			if($chest !== null){
				$chest->close($this->getPlayer());
			}
			return false;
		}
		return true;
	}
	public function onChatEvent(/** @noinspection PhpUnusedParameterInspection */
		PlayerChatEvent $event){
		$msg = $event->getMessage();
		$this->onChat($msg, self::CHAT_NORMAL_CLASS);
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
		if($this->walkingParticle instanceof WalkingParticle){
			unset($this->getMain()->walkingParticles[$this->walkingParticle->getId()]);
		}
		$this->saveData();
	}

	public function saveData($newStatus = Settings::STATUS_OFFLINE){
		if($this->state === self::STATE_PLAYING){ // don't save if not registered/logged in or transferring
			$this->getMain()->saveSessionData($this, $newStatus);
		}
	}
	/**
	 * @param string $key
	 * @param int $amplitude
	 * @return int
	 */
	public function incrementLoginDatum($key, $amplitude = 1){
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
	public function grantCoins($coins, $ignoreGrind = false, $effects = true){
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
			for($i = 0; $i < 50; $i++){
				$x = $random->nextSignedFloat();
				$y = $random->nextSignedFloat() * $player->eyeHeight / 2;
				$z = $random->nextSignedFloat();
				$particle->setComponents(
					$player->x + $x,
					$player->y + $player->eyeHeight / 2 + $y,
					$player->z + $z
				);
				$level->addParticle($particle, $recipients);
			}
		}
		$this->setCoins($out = $this->getCoins() + $coins);
		return [$coins, $out];
	}
	public function isGrinding(){
		return time() - $this->getLastGrind() <= Settings::getGrindLength($this->getRank());
	}
	public function getLastGrind(){
		return $this->getLoginDatum("lastgrind");
	}
	public function setCoins($coins){
		$this->setLoginDatum("coins", max(0, $coins));
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
		return (bool) ($this->getLoginDatum("config") & Settings::CONFIG_TAG_ON);
	}
	public function isStatsPublic(){
		return (bool) ($this->getLoginDatum("config") & Settings::CONFIG_STATS_PUBLIC);
	}
	public function isTeamChannelOn(){
		return (bool) ($this->getLoginDatum("config") & Settings::CONFIG_TEAM_CHANNEL_ON);
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
		return $this->getLoginDatum("emailauth") === 2;
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
		if(time() - $this->getLastWarnTime() < 10){
			$issuer->sendMessage("User has been warned in the past 10 seconds!");
		}
		/** @noinspection PhpDeprecationInspection */
		new PreExecuteWarningQuery($this->getMain(), $this->getUid(), $this->getPlayer()->getAddress(), $this->getPlayer()->getClientId(), $id, $points, $issuer, $msg);
	}
	public function getTeamJoinTime(){
		return $this->getLoginDatum("teamjoin");
	}
	public function getTeamPoints(){
		return $this->getLoginDatum("teampts");
	}
	public function grantTeamPoints($points = 1){
		$this->incrementLoginDatum("teampts", $points);
	}
	public function takeTeamPoints($points = 1){
		$pts = $this->getTeamPoints();
		$pts = max(0, $pts - $points);
		$this->setLoginDatum("teampts", $pts);
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
	public function isOnChannel($channel, &$subLevel = null){
		$channels = array_change_key_case($this->getLoginDatum("channels"), CASE_LOWER);
		if(isset($channels[$channel = strtolower($channel)])){
			$subLevel = $channels[$channel];
			return true;
		}
		return false;
	}
	public function joinChannel($channel, $subLevel = self::CHANNEL_SUB_NORMAL){
		$subs = $this->getChannelSubscriptions();
		if(isset(array_change_key_case($subs, CASE_LOWER)[strtolower($channel)])){
			return false;
		}
		$subs[$channel] = $subLevel;
		$this->setLoginDatum("channels", $subs);
		new JoinChannelQuery($this->getMain(), $this->getUid(), $channel, $subLevel);
		$type = Hormone::get($this->getMain(), Hormone::CHANNEL_CHAT, $this->getPlayer()->getName(), "%tr%" . Phrases::CMD_CHANNEL_JOINED_OTHER, Settings::CLASS_ALL, [
			"channel" => $channel,
			"fromClass" => Settings::$LOCALIZE_CLASS,
			"ign" => $this->inGameName,
			"level" => self::CHANNEL_SUB_VERBOSE,
			"data" => [
				"player" => $this->getInGameName(),
				"ip" => Settings::$LOCALIZE_IP,
				"port" => Settings::$LOCALIZE_PORT,
				"channel" => $channel
			]
		]);
		$type->release();
		return true;
	}
	public function getChannelSubscriptions(){
		return $this->getLoginDatum("channels");
	}
	public function partChannel($channel){
		$subs = $this->getChannelSubscriptions();
		$lowerChannels = array_keys(array_change_key_case($subs, CASE_LOWER));
		$pos = array_search(strtolower($channel), $lowerChannels);
		if($pos !== false){
			$caseName = array_keys($subs)[$pos];
			unset($subs[$caseName]);
			$this->setLoginDatum("channels", $subs);
			new PartChannelQuery($this->getMain(), $this->getUid(), $caseName);
			$type = Hormone::get($this->getMain(), Hormone::CHANNEL_CHAT, $this->getPlayer()->getName(), "%tr%" . Phrases::CMD_CHANNEL_QUITTED, Settings::CLASS_ALL, [
				"channel" => $channel,
				"fromClass" => Settings::$LOCALIZE_CLASS,
				"ign" => $this->inGameName,
				"level" => self::CHANNEL_SUB_VERBOSE,
				"data" => [
					"player" => $this->getInGameName(),
					"ip" => Settings::$LOCALIZE_IP,
					"port" => Settings::$LOCALIZE_PORT,
					"channel" => $channel
				]
			]);
			$type->release();
		}
	}
	/**
	 * @param int $flags
	 * @return Friend[]
	 */
	public function getFriends($flags = Friend::FLAG_ALL){
		$ret = [];
		foreach($this->getLoginDatum("friends") as $type => $friends){
			if(!($flags & $type)){
				continue;
			}
			/** @var Friend $friend */
			foreach($friends as $friend){
				if(($flags & Friend::FLAG_IN_ONLY) and $friend->isRequestOut()){
					continue;
				}
				if(($flags & Friend::FLAG_OUT_ONLY) and !$friend->isRequestOut()){
					continue;
				}
				$ret[$friend->friendUid] = $friend;
			}
		}
		return $ret;
	}
	public function getFriend($uid, &$hasRow = false){
		foreach($this->getLoginDatum("friends") as $type => $friends){
			if(isset($friends[$uid])){
				$hasRow = true;
				return $friends[$uid];
			}
		}
		$hasRow = false;
		return new Friend($this->getUid(), $uid, Friend::FRIEND_NOT_FRIEND, Friend::FRIEND_NOT_FRIEND, Friend::DIRECTION_NIL, "");
	}
	public function setFriendAttempt($otherUid, $type = Friend::FRIEND_GOOD_FRIEND, &$prop){
		$prop = true;
		if($otherUid === $this){
			$prop = false;
			return Friend::RET_SAME_UID;
		}
		$friend = $this->getFriend($otherUid, $update);
		$dir = $friend->getRequestRelativeDirection();
		$current = $friend->type;
		$requested = $friend->requestedType;
		$outDirection = ($this->getUid() > $otherUid) ? Friend::DIRECTION_BIG_TO_SMALL : Friend::DIRECTION_SMALL_TO_BIG;
		$small = min($this->getUid(), $otherUid);
		$large = max($this->getUid(), $otherUid);
		$condition = "WHERE smalluid=$small AND largeuid=$large";
		$NOT_FRIEND = Friend::FRIEND_NOT_FRIEND;
		$NIL = Friend::DIRECTION_NIL;
		// MEMO: $requested > $current
		if($dir === Friend::DIRECTION_NIL){
			if($type === $current){
				$prop = false;
				return Friend::RET_IS_CURRENT_STATE;
			}elseif($type > $current){
				new RawAsyncQuery($this->getMain(), $update ? "UPDATE friends SET requested=$type, direction=$outDirection $condition" : "INSERT INTO friends (smalluid, largeuid, type, requested, direction) VALUES ($small, $large, $NOT_FRIEND, $type, $outDirection)");
				return Friend::RET_SENT_REQUEST;
			}else{
				Friend::countFriends($this->getMain()->getDb(), $type, $fulls, $otherUid, $this->getUid());
				if(isset($fulls[$this->getUid()])){
					return Friend::RET_ME_FULL;
				}elseif(count($fulls) > 0){
					return Friend::RET_OTHER_FULL;
				}
				new RawAsyncQuery($this->getMain(), $update ? "UPDATE friends SET type=$type, requested=$NOT_FRIEND, direction=$NIL $condition" : "INSERT INTO friends (smalluid, largeuid, type, requested, direction) VALUES ($small, $large, $type, $NOT_FRIEND, $NIL)");
				return Friend::RET_REDUCED;
			}
		}elseif($dir === Friend::DIRECTION_OUT){
			if($type === $requested){
				$prop = false;
				return Friend::RET_REQUEST_ALREADY_SENT;
			}elseif($type > $requested){ // i.e. $type > $current
				new RawAsyncQuery($this->getMain(), "UPDATE friends SET requested=$type $condition");
				return Friend::RET_RAISED_REQUEST;
			}else{
				if($type > $current){
					new RawAsyncQuery($this->getMain(), "UPDATE friends SET requested=$type $condition");
					return Friend::RET_REQUEST_REDUCED;
				}elseif($type === $current){
					new RawAsyncQuery($this->getMain(), "DELETE FROM friends $condition");
					return Friend::RET_REQUEST_CANCELLED;
				}else{
					Friend::countFriends($this->getMain()->getDb(), $type, $fulls, $otherUid, $this->getUid());
					if(isset($fulls[$this->getUid()])){
						return Friend::RET_ME_FULL;
					}elseif(count($fulls) > 0){
						return Friend::RET_OTHER_FULL;
					}
					new RawAsyncQuery($this->getMain(), "UPDATE friends SET type=$type, requested=$NOT_FRIEND, direction=$NIL $condition");
					return Friend::RET_REQUEST_CANCELLED_AND_REDUCED;
				}
			}
		}else{ // $dir = Friend::DIRECTION_IN
			if($type === $requested){
				Friend::countFriends($this->getMain()->getDb(), $type, $fulls, $otherUid, $this->getUid());
				if(isset($fulls[$this->getUid()])){
					return Friend::RET_ME_FULL;
				}elseif(count($fulls) > 0){
					return Friend::RET_OTHER_FULL;
				}
				new RawAsyncQuery($this->getMain(), "UPDATE friends SET type=$type, requested=$type, direction=$NIL $condition");
				return Friend::RET_REQUEST_ACCEPTED;
			}elseif($type > $requested){ // $type > $requested > $current
				Friend::countFriends($this->getMain()->getDb(), $type, $fulls, $otherUid, $this->getUid());
				if(isset($fulls[$this->getUid()])){
					return Friend::RET_ME_FULL;
				}elseif(count($fulls) > 0){
					return Friend::RET_OTHER_FULL;
				}
				new RawAsyncQuery($this->getMain(), "UPDATE friends SET type=$requested, requested=$type, direction=$outDirection $condition");
				return Friend::RET_REQUEST_ACCEPTED_AND_RAISE_SENT;
			}else{ // $requested > $type <=> $current
				if($type === $current){
					new RawAsyncQuery($this->getMain(), "UPDATE friends SET requested=$NOT_FRIEND, direction=$NIL $condition");
					return Friend::RET_REQUEST_REJECTED;
				}elseif($type > $current){
					new RawAsyncQuery($this->getMain(), "UPDATE friends SET requested=$type, direction=$outDirection $condition");
					return Friend::RET_REQUEST_REJECTED_AND_LOWER_SENT;
				}else{
					Friend::countFriends($this->getMain()->getDb(), $type, $fulls, $otherUid, $this->getUid());
					if(isset($fulls[$this->getUid()])){
						return Friend::RET_ME_FULL;
					}elseif(count($fulls) > 0){
						return Friend::RET_OTHER_FULL;
					}
					new RawAsyncQuery($this->getMain(), "UPDATE friends SET type=$type, requested=$NOT_FRIEND, direction=$NIL $condition");
					return Friend::RET_REQUEST_REJECTED_AND_REDUCED;
				}
			}
		}
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
	public function setSessionState($state){
		$this->state = $state;
	}
	/**
	 * @return string
	 */
	public function getCurrentChatState(){
		return $this->currentChatState;
	}
	/**
	 * @param string $currentChatState
	 */
	public function setCurrentChatState($currentChatState){
		$this->currentChatState = $currentChatState;
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
		$this->sendMaintainedPopup();
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
		$type = MuteHormone::fromObject($this->getMain(), $mute);
		$type->release();
		return $mute;
	}
	public static function hash($password, $uid){
		return bin2hex(hash("whirlpool", $password . $uid, true) ^ hash("sha512", $uid . $password, true));
	}
	public static function oldHash($password){
		return bin2hex(hash("sha512", $password . "NaCl", true) ^ hash("whirlpool", "NaCl" . $password, true));
	}
	public function sendMessage($msg, $args = []){
		if(substr($msg, 0, 4) === "%tr%"){
			$this->send(substr($msg, 4), $args);
		}else{
			$this->getPlayer()->sendMessage($msg);
		}
	}
	public function reloadKits(){
	}
	public function reloadKitsCallback(){
	}
	/**
	 * @return Session|null
	 */
	public function getQueryTarget(){
		if($this->queryTarget === null){
			return null;
		}
		return $this->getMain()->getSessionByUid($this->queryTarget);
	}
	/**
	 * @param int|null $uid
	 */
	public function setQueryTargetUid($uid){
		$this->queryTarget = $uid;
	}
	protected function sendMaintainedPopup(){
		if($this->curPopup !== null){
			$this->getPlayer()->sendPopup($this->curPopup);
		}
	}
}
