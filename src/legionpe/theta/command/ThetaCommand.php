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

namespace legionpe\theta\command;

use legionpe\theta\BasePlugin;
use legionpe\theta\command\admin\PrivateNoticeCommand;
use legionpe\theta\command\admin\TeleportServerPlayerCommand;
use legionpe\theta\command\admin\WarnCommand;
use legionpe\theta\command\override\MBCommand;
use legionpe\theta\command\override\OverridingKillCommand;
use legionpe\theta\command\override\OverridingMeCommand;
use legionpe\theta\command\override\OverridingSayCommand;
use legionpe\theta\command\override\OverridingStatusCommand;
use legionpe\theta\command\override\OverridingTellCommand;
use legionpe\theta\command\override\OverridingVersionCommand;
use legionpe\theta\command\session\ChannelCommand;
use legionpe\theta\command\session\CoinsCommand;
use legionpe\theta\command\session\ConsoleCommand;
use legionpe\theta\command\session\DirectTeleportCommand;
use legionpe\theta\command\session\friend\FallbackFriendCommand;
use legionpe\theta\command\session\friend\FriendListCommand;
use legionpe\theta\command\session\friend\SetFriendCommand;
use legionpe\theta\command\session\GrindCoinCommand;
use legionpe\theta\command\session\LabelCommand;
use legionpe\theta\command\session\QueryCommand;
use legionpe\theta\command\session\team\FallbackTeamCommand;
use legionpe\theta\command\session\team\TeamConfigCommand;
use legionpe\theta\command\session\team\TeamCreateCommand;
use legionpe\theta\command\session\team\TeamInfoCommand;
use legionpe\theta\command\session\team\TeamInviteCommand;
use legionpe\theta\command\session\team\TeamJoinCommand;
use legionpe\theta\command\session\team\TeamKickCommand;
use legionpe\theta\command\session\team\TeamListCommand;
use legionpe\theta\command\session\team\TeamMemberCommand;
use legionpe\theta\command\session\team\TeamPropertyChangeCommand;
use legionpe\theta\command\session\team\TeamQuitCommand;
use legionpe\theta\command\session\team\TeamRankChangeCommand;
use legionpe\theta\command\session\TransferCommand;
use legionpe\theta\config\Settings;
use legionpe\theta\Friend;
use legionpe\theta\lang\Phrases;
use legionpe\theta\Session;
use pocketmine\command\Command;
use pocketmine\command\CommandMap;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

abstract class ThetaCommand extends Command implements PluginIdentifiableCommand{
	/** @var BasePlugin */
	private $plugin;
	/**
	 * @param BasePlugin $plugin
	 * @param string $name
	 * @param string $desc
	 * @param string $usage
	 * @param string[] $aliases
	 */
	public function __construct(BasePlugin $plugin, $name, $desc, $usage, $aliases = []){
		parent::__construct($name, $desc, $usage, (array) $aliases);
		$this->plugin = $plugin;
	}
	public static function registerAll(BasePlugin $main, CommandMap $map){
		foreach(
			[
				"version",
				"stop",
				"tell",
				"w",
				"msg",
				"defaultgamemode",
				"ban",
				"ban-ip",
				"banlist",
				"pardon",
				"pardon-ip",
				"say",
				"me",
				"difficulty",
				"kick",
				"op",
				"deop",
				"whitelist",
				"save-on",
				"save-off",
				"save-all",
				"spawnpoint",
				"setworldspawn",
				"tp",
				"reload",
				"status",
				"kill"
			] as $cmd){
			self::unregisterCommand($map, $cmd);
		}
		$map->registerAll("l", [
			new CoinsCommand($main),
			new PhpCommand($main),
			new OverridingKillCommand($main, "kill", "Commit suicide", "/kill", ["suicide"]),
			new OverridingStatusCommand($main),
			new OverridingTellCommand($main),
			new PrivateNoticeCommand($main),
			new OverridingVersionCommand($main),
			new ConsoleCommand($main),
			new DirectTeleportCommand($main),
			new TeleportServerPlayerCommand($main),
			new GrindCoinCommand($main),
			new LabelCommand($main),
			new MBCommand($main),
			new ChannelCommand($main),
			new OverridingMeCommand($main),
			new GetPositionCommand($main),
			new TransferCommand($main, ["pvp", "kitpvp"], "Kit PvP", Settings::CLASS_KITPVP),
			new TransferCommand($main, ["parkour", "pk"], "Parkour", Settings::CLASS_PARKOUR),
			new TransferCommand($main, ["spleef", "spf"], "Spleef", Settings::CLASS_SPLEEF),
			new TransferCommand($main, ["infected", "inf"], "Infected", Settings::CLASS_INFECTED),
			new TransferCommand($main, ["shop", "shops"], "Shops", Settings::CLASS_SHOPS),
			new TransferCommand($main, ["classic", "cls"], "Classic PvP", Settings::CLASS_CLASSICAL),
			new TransferCommand($main, ["hub", "spawn", "quit", "home", "back", "lobby"], "Hub", Settings::CLASS_HUB),
			new OverridingSayCommand($main),
			new QueryCommand($main),
			new RestartCommand($main),
			new FallbackFriendCommand($main),
			new SetFriendCommand($main, Friend::FRIEND_ENEMY),
			new SetFriendCommand($main, Friend::FRIEND_NOT_FRIEND),
			new SetFriendCommand($main, Friend::FRIEND_ACQUAINTANCE),
			new SetFriendCommand($main, Friend::FRIEND_GOOD_FRIEND),
			new SetFriendCommand($main, Friend::FRIEND_BEST_FRIEND),
			new FriendListCommand($main),
			new TeamCreateCommand($main),
			new TeamInviteCommand($main),
			new TeamJoinCommand($main),
			new TeamQuitCommand($main),
			new TeamKickCommand($main),
			new TeamRankChangeCommand($main, true),
			new TeamRankChangeCommand($main, false),
			new TeamPropertyChangeCommand($main, ["tdesc", "tdescr"], Phrases::WORDS_TEAM_PROPERTY_DESCRIPTION, "descr"),
			new TeamPropertyChangeCommand($main, ["trule", "trules"], Phrases::WORDS_TEAM_PROPERTY_RULES, "rules"),
			new TeamPropertyChangeCommand($main, ["treq", "trequire", "trequires", "trequirement", "trequirements"], Phrases::WORDS_TEAM_PROPERTY_REQUIREMENTS, "req"),
			new TeamConfigCommand($main, Settings::TEAM_CONFIG_OPEN, true, Phrases::WORDS_TEAM_CONFIG_OPEN, ["topen"]),
			new TeamConfigCommand($main, Settings::TEAM_CONFIG_OPEN, false, Phrases::WORDS_TEAM_CONFIG_OPEN, ["tclose"]),
			new TeamListCommand($main),
			new TeamMemberCommand($main),
			new TeamInfoCommand($main),
			new FallbackTeamCommand($main),
			new WarnCommand($main, ["mod", "mods"], "using mods", WarnCommand::MODS),
			new WarnCommand($main, ["swear", "curse"], "swearing or harassing", WarnCommand::SWEAR),
			new WarnCommand($main, ["cap", "caps"], "using abusive caps", WarnCommand::CAPS),
			new WarnCommand($main, ["ad", "ads"], "advertizing", WarnCommand::ADS),
			new WarnCommand($main, ["do", "disobey"], "refusing to follow instructions", WarnCommand::DISOBEY),
			new WarnCommand($main, ["impose", "imp"], "staff imposing", WarnCommand::IMPOSE),
			new WarnCommand($main, ["spam"], "spamming", WarnCommand::SPAM),
			new WarnCommand($main, ["misc"], "miscellaneous warnings", WarnCommand::MISC),
		]);
	}
	private static function unregisterCommand(CommandMap $map, $name){
		$cmd = $map->getCommand($name);
		if($cmd instanceof Command){
			$cmd->setLabel($name . "_deprecated");
			$cmd->unregister($map);
			return true;
		}
		return false;
	}
	/**
	 * Alias of {@link #getPlugin}
	 * @return BasePlugin
	 */
	public function getMain(){
		return $this->plugin;
	}
	protected function sendUsage($sender){
		if($sender instanceof Player){
			if(($ses = $this->getSession($sender)) instanceof Session){
				$sender = $ses;
			}
		}
		if($sender instanceof Session){
			$sender->send(Phrases::CMD_ERR_WRONG_USE, ["usage" => $this->getUsage()]);
			return;
		}
		if($sender instanceof CommandSender){
			$sender->sendMessage(TextFormat::RED . "Usage: " . $this->getUsage());
		}
	}
	public function getSession($player){
		return $this->getPlugin()->getSession($player);
	}
	/**
	 * @return BasePlugin
	 */
	public function getPlugin(){
		return $this->plugin;
	}
	/**
	 * @param Session|CommandSender $sender
	 * @param string|null $name
	 * @return bool
	 */
	protected function notOnline($sender, $name = null){
		if($sender instanceof Session){
			if($name === null){
				$sender->send(Phrases::CMD_ERR_ABSENT_PLAYER_NAME_UNKNOWN);
			}else{
				$sender->send(Phrases::CMD_ERR_ABSENT_PLAYER_NAME_KNOWN, ["player" => $name]);
			}
			return true;
		}
		if($sender instanceof CommandSender){
			if($sender instanceof Player and ($ses = $this->getSession($sender)) instanceof Session){
				if($name === null){
					$ses->send(Phrases::CMD_ERR_ABSENT_PLAYER_NAME_UNKNOWN);
				}else{
					$ses->send(Phrases::CMD_ERR_ABSENT_PLAYER_NAME_KNOWN, ["player" => $name]);
				}
			}else{
				$sender->sendMessage(TextFormat::RED . "There is no player online with " .
					($name === null ? "that name" : "the name $name") . ".");
			}
		}
		return true;
	}
	/**
	 * Broadcast a message to all moderators (including trial) on the server
	 * @param string $msg
	 * @param bool $translate
	 * @param array $args
	 */
	protected function broadcastModerator($msg, $translate = true, $args = []){
		foreach($this->getPlugin()->getSessions() as $ses){
			if($ses->isModerator()){
				if($translate){
					$ses->send($msg, $args);
				}else{
					$ses->getPlayer()->sendMessage($msg);
				}
			}
		}
	}
}
