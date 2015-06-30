<?php

/**
 * Theta
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
	const LOGIN_REGISTER_PROMPT = "login.register.prompt";
	const LOGIN_REGISTER_RETYPE = "login.register.retype";
	const LOGIN_REGISTER_SUCCESS = "login.register.success";
	const LOGIN_REGISTER_MISMATCH = "login.register.mismatch";
	const LOGIN_PASS_PROMPT = "login.pass.prompt";
	const LOGIN_PASS_MISMATCH = "login.pass.mismatch";

	const CHAT_BLOCKED_PASS = "chat.blocked.pass";
	const CHAT_BROADCASTS_ARRAY = "chat.broadcasts";
	const CHAT_FORMAT_TEAM = "chat.format.team";
	const CHAT_FORMAT_CHANNEL = "chat.format.channel";
	const CHAT_FORMAT_LOCAL = "chat.format.local";
	const CHAT_FORMAT_BROADCAST_NETWORK = "chat.format.broadcast.network";
	const CHAT_FORMAT_BROADCAST_CLASS = "chat.format.broadcast.class";
	const CHAT_FORMAT_BROADCAST_LOCAL = "chat.format.broadcast.local";

	const CMD_ERR_NO_PERM = "cmd.error.noperm";
	const CMD_ERR_WRONG_USE = "cmd.error.wronguse";
	const CMD_ERR_ABSENT_PLAYER_NAME_UNKNOWN = "cmd.error.absplayer.unknown";
	const CMD_ERR_ABSENT_PLAYER_NAME_KNOWN = "cmd.error.absplayer.named";
	const CMD_GRIND_COIN_CANNOT_START = "cmd.gc.cannotstart";
	const CMD_GRIND_COIN_REQUEST_CONFIRM = "cmd.gc.reqconfirm";
	const CMD_GRIND_COIN_STARTED = "cmd.gc.started";
	const CMD_PRIV_MSG_REMIND_QUERY = "cmd.pm.remindquery";
	const CMD_PRIV_NOTICE_RECIPIENT = "cmd.pn.recipient";
	const CMD_TRANSFER_ERR_NO_SERVERS = "cmd.transfer.err.noservers";
	const CMD_TRANSFER_SUCCESS = "cmd.transfer.success";
	const CMD_VERSION_MSG = "cmd.version.msg";

	const WARNING_RECEIVED_NOTIFICATION = "warning.notification.main";
	const WARNING_MUTED_NOTIFICATION = "warning.notification.muted";
	const WARNING_BANNED_NOTIFICATION = "warning.notification.banned";

	const KICK_TOO_LONG_ONLINE = "kick.toolongonline";

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

	// constant variables
	const VAR_wait = TextFormat::RED . "… ";
	const VAR_success = TextFormat::GREEN;
	const VAR_info = TextFormat::WHITE;
	const VAR_symbol = TextFormat::GRAY;
	const VAR_verbose = "》 " . self::VAR_verbosemid;
	const VAR_verbosemid = TextFormat::GRAY;
	const VAR_error = TextFormat::DARK_RED;
	const VAR_warning = TextFormat::YELLOW;
	const VAR_notify = TextFormat::LIGHT_PURPLE;
	const VAR_notify2 = TextFormat::GOLD;
	const VAR_em = TextFormat::AQUA;
	const VAR_em2 = TextFormat::BLUE;
	const VAR_em3 = TextFormat::DARK_BLUE;
	const VAR_reset = TextFormat::RESET;
	const VAR_bold = TextFormat::BOLD;
	const VAR_italic = TextFormat::ITALIC;
}
