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

namespace legionpe\theta\lang;

use pocketmine\utils\TextFormat;

class Phrases{
	// phrases
	const META_SHORT = "meta.short";
	const META_ENGLISH = "meta.english";
	const META_LOCAL = "meta.local";
	const META_AUTHORS = "meta.authors";
	const META_VERSION = "meta.version";

	const LOGIN_AUTH_SUCCESS = "login.auth.success";
	const LOGIN_AUTH_WHEREAMI = "login.auth.whereami";
	const LOGIN_KNOWN_AS = "login.knownas";
	const LOGIN_PASS_PROMPT = "login.pass.prompt";
	const LOGIN_PASS_MISMATCH = "login.pass.mismatch";
	const LOGIN_REGISTER_PROMPT = "login.register.prompt";
	const LOGIN_REGISTER_RETYPE = "login.register.retype";
	const LOGIN_REGISTER_SUCCESS = "login.register.success";
	const LOGIN_REGISTER_MISMATCH = "login.register.mismatch";

	const CHAT_BLOCKED_PASS = "chat.blocked.pass";
	const CHAT_BLOCKED_TOO_FAST = "chat.blocked.toofast";
	const CHAT_BLOCKED_TOO_FREQUENT = "chat.blocked.toofreq";
	const CHAT_BLOCKED_TOO_SHORT = "chat.blocked.tooshort";
	const CHAT_BROADCASTS_ARRAY = "chat.broadcasts";
	const CHAT_FORMAT_TEAM = "chat.format.team";
	const CHAT_FORMAT_CHANNEL = "chat.format.channel";
	const CHAT_FORMAT_LOCAL = "chat.format.local";
	const CHAT_FORMAT_BROADCAST_NETWORK = "chat.format.broadcast.network";
	const CHAT_FORMAT_BROADCAST_CLASS = "chat.format.broadcast.class";
	const CHAT_FORMAT_BROADCAST_LOCAL = "chat.format.broadcast.local";

	const CMD_ERR_NO_PERM = "cmd.error.noperm.normal";
	const CMD_ERR_NO_PERM_DONATE = "cmd.error.noperm.donate";
	const CMD_ERR_WRONG_USE = "cmd.error.wronguse";
	const CMD_ERR_ABSENT_PLAYER_NAME_UNKNOWN = "cmd.error.absplayer.unknown";
	const CMD_ERR_ABSENT_PLAYER_NAME_KNOWN = "cmd.error.absplayer.named";
	const CMD_ERR_LOADING = "cmd.error.loading";

	const CMD_CHANNEL_VIEW_LOCAL = "cmd.channel.view.local";
	const CMD_CHANNEL_VIEW_TEAM = "cmd.channel.view.team";
	const CMD_CHANNEL_VIEW_OTHER = "cmd.channel.view.other";
	const CMD_CHANNEL_VIEW_SUBSCRIBING_TO = "cmd.channel.view.subto";
	const CMD_CHANNEL_SET_LOCAL = "cmd.channel.set.local";
	const CMD_CHANNEL_SET_TEAM = "cmd.channel.set.team";
	const CMD_CHANNEL_SET_OTHER = "cmd.channel.set.other";
	const CMD_CHANNEL_QUIT_NOT_ON_CHANNEL = "cmd.channel.quit.notsub";
	const CMD_CHANNEL_QUIT_SUCCESS = "cmd.channel.quit.success";
	const CMD_CHANNEL_QUITTED = "cmd.channel.quitted";
	const CMD_CHANNEL_JOINED_SELF = "cmd.channel.joined.self";
	const CMD_CHANNEL_JOINED_OTHER = "cmd.channel.joined.other";

	const CMD_FRIEND_ALREADY_INVITED = "cmd.friend.alreadyinvited";
	const CMD_FRIEND_RAISE_REQUESTED = "cmd.friend.requested.raise";
	const CMD_FRIEND_REQUEST_REMOVED = "cmd.friend.requested.removed";
	const CMD_FRIEND_RECEIVED = "cmd.friend.received";
	const CMD_FRIEND_RAISED = "cmd.friend.raised";
	const CMD_FRIEND_REDUCED = "cmd.friend.reduced";
	const CMD_FRIEND_MAX = "cmd.friend.max";
	const CMD_FRIEND_REJECTED = "cmd.friend.rejected";
	const CMD_FRIEND_CANCELLED = "cmd.friend.cancelled";
	const CMD_FRIEND_NO_INVITATION = "cmd.friend.noinvite";
	const CMD_FRIEND_LIST_KEY = "cmd.friend.list.key";

	const CMD_GRIND_COIN_CANNOT_START = "cmd.gc.cannotstart";
	const CMD_GRIND_COIN_REQUEST_CONFIRM = "cmd.gc.reqconfirm";
	const CMD_GRIND_COIN_STARTED = "cmd.gc.started";
	const CMD_GRIND_COIN_ADVICE = "cmd.gc.advice";

	const CMD_LABEL_VIEW = "cmd.lbl.view";
	const CMD_LABEL_WAIT_FOR_APPROVAL = "cmd.lbl.approving";
	const CMD_LABEL_CHANGED = "cmd.lbl.changed";

	const CMD_PRIV_MSG_REMIND_QUERY = "cmd.pm.remindquery";

	const CMD_PRIV_NOTICE_RECIPIENT = "cmd.pn.recipient";

	const CMD_TEAM_CREATE_INVALID_NAME = "cmd.team.create.invalidname";
	const CMD_TEAM_ERR_ALREADY_IN_TEAM = "cmd.team.err.alreadyinteam";
	const CMD_TEAM_ERR_NOT_IN_TEAM = "cmd.team.err.notinteam";
	const CMD_TEAM_CREATE_ALREADY_EXISTS = "cmd.team.create.alreadyexists";
	const CMD_TEAM_CREATE_SUCCESS = "cmd.team.create.success";
	const CMD_TEAM_LOADING = "cmd.team.loading";
	const CMD_TEAM_JOIN_ALREADY_REQUESTED = "cmd.team.join.alreadysent";
	const CMD_TEAM_JOIN_REQUESTED = "cmd.team.join.requested";
	const CMD_TEAM_JOIN_ACCEPTED = "cmd.team.join.accepted";
	const CMD_TEAM_JOIN_DIRECTLY_JOINED = "cmd.team.join.joined";
	const CMD_TEAM_QUIT_WARNING_JUNIOR = "cmd.team.quit.warning.junior";
	const CMD_TEAM_QUIT_WARNING_NORMAL = "cmd.team.quit.warning.normal";
	const CMD_TEAM_QUIT_WARNING_LEADER = "cmd.team.quit.warning.leader";
	const CMD_TEAM_INVITE_NOT_SENIOR = "cmd.team.invite.notsenior";
	const CMD_TEAM_INVITE_NO_PLAYER = "cmd.team.invite.noplayer";
	const CMD_TEAM_INVITE_TARGET_IN_TEAM = "cmd.team.invite.targetinteam";
	const CMD_TEAM_INVITE_ACCEPT_TARGET_IN_TEAM = "cmd.team.invite.accepttargetinteam";
	const CMD_TEAM_INVITE_ALREADY_SENT = "cmd.team.invite.alreadysent";
	const CMD_TEAM_INVITE_SAME_TEAM = "cmd.team.invite.sameteam";
	const CMD_TEAM_INVITE_ACCEPTED_SENDER = "cmd.team.invite.accepted.sender";
	const CMD_TEAM_INVITE_ACCEPTED_TARGET = "cmd.team.invite.accepted.target";
	const CMD_TEAM_INVITE_SENT = "cmd.team.invite.sent";
	const CMD_TEAM_JOINED = "cmd.team.joined";
	const CMD_TEAM_QUITTED = "cmd.team.quitted";

	const CMD_TRANSFER_ERR_NO_SERVERS = "cmd.transfer.err.noservers";
	const CMD_TRANSFER_SUCCESS = "cmd.transfer.success";

	const CMD_VERSION_MSG = "cmd.version.msg";

	const WARNING_RECEIVED_NOTIFICATION = "warning.notification.main";
	const WARNING_MUTED_NOTIFICATION = "warning.notification.muted";
	const WARNING_BANNED_NOTIFICATION = "warning.notification.banned";

	const KICK_TOO_LONG_ONLINE = "kick.toolong.online";
	const KICK_TOO_LONG_LOGIN = "kick.toolong.login";

	const AUTH_METHOD_TRANSFER = "login.auth.method.transfer";
	const AUTH_METHOD_UUID = "login.auth.method.uuid";
	const AUTH_METHOD_IP_LAST = "login.auth.method.ip.last";
	const AUTH_METHOD_IP_HIST = "login.auth.method.ip.hist";
	const AUTH_METHOD_PASS = "login.auth.method.pass";
	const AUTH_METHOD_REG = "login.auth.method.register";

	const CLASS_HUB = "local.class.name.hub";
	const CLASS_KITPVP = "local.class.name.pvp.kit";
	const CLASS_PARKOUR = "local.class.name.parkour";
	const CLASS_SPLEEF = "local.class.name.spleef";
	const CLASS_INFECTED = "local.class.name.infected";
	const CLASS_CLASSIC_PVP = "local.class.name.pvp.classic";

	const FRIEND_ACQUAINTANCE = "friend.type.acquaintance";
	const FRIEND_GOOD_FRIEND = "friend.type.goodfriend";
	const FRIEND_BEST_FRIEND = "friend.type.bestfriend";

	const PVP_DEATH_GENERIC = "game.pvp.death.generic";
	const PVP_DEATH_KILLED = "game.pvp.death.killed";
	const PVP_DEATH_FALL_GENERIC = "game.pvp.death.fall.generic";
	const PVP_DEATH_FALL_LADDER = "game.pvp.death.fall.ladder";
	const PVP_DEATH_FALL_VINE = "game.pvp.death.fall.vine";
	const PVP_DEATH_STATS = "game.pvp.death.stats";
	const PVP_KILL_GENERIC = "game.pvp.kill.generic";
	const PVP_KILL_KILLED = "game.pvp.kill.killed";
	const PVP_KILL_FALL_GENERIC = "game.pvp.kill.fall.generic";
	const PVP_KILL_FALL_LADDER = "game.pvp.kill.fall.ladder";
	const PVP_KILL_FALL_VINE = "game.pvp.kill.fall.vine";
	const PVP_KILL_STATS = "game.pvp.kill.stats";
	const PVP_ACTION_GENERIC = "game.pvp.action.kill";
	const PVP_ACTION_ARROW = "game.pvp.action.arrow";
	const PVP_ACTION_SNOWBALL = "game.pvp.action.snowball";
	const PVP_ACTION_EGG = "game.pvp.action.egg";

	const PVP_ATTACK_FRIENDS = "game.pvp.attack.friends";
	const PVP_ATTACK_SPAWN = "game.pvp.attack.spawn";

	const PVP_KILL_INFO = "game.pvp.killinfo";
	const PVP_DEATH_INFO = "game.pvp.deathinfo";

	const HUB_FEATHER_TUT = "game.hub.feather.tut";

	// constant variables
	const VAR_wait = TextFormat::RED . "… ";
	const VAR_success = TextFormat::DARK_GREEN;
	const VAR_info = TextFormat::WHITE;
	const VAR_symbol = TextFormat::GRAY;
	const VAR_verbose = "》 " . self::VAR_verbosemid;
	const VAR_verbosemid = TextFormat::GRAY;
	const VAR_error = TextFormat::DARK_RED;
	const VAR_warning = TextFormat::YELLOW;
	const VAR_notify = TextFormat::LIGHT_PURPLE;
	const VAR_notify2 = TextFormat::GOLD;
	const VAR_em = TextFormat::AQUA;
	const VAR_em1 = TextFormat::AQUA;
	const VAR_em2 = TextFormat::BLUE;
	const VAR_em3 = TextFormat::DARK_BLUE;
	const VAR_reset = TextFormat::RESET;
	const VAR_bold = TextFormat::BOLD;
	const VAR_italic = TextFormat::ITALIC;
}
