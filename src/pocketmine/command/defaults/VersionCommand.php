<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class VersionCommand extends VanillaCommand
{
    public function __construct($name)
    {
        parent::__construct(
            $name,
            "%pocketmine.command.version.description",
            "%pocketmine.command.version.usage",
            ["ver", "about", "versao"]
        );
        $this->setPermission("pocketmine.command.version");
    }

    public function execute(CommandSender $sender, $currentAlias, array $args)
    {
        if (!$this->testPermission($sender)) {
            return true;
        }

        $server = $sender->getServer();
        $line = TextFormat::DARK_GREEN . "========================================";
        $sender->sendMessage($line);
        $sender->sendMessage(TextFormat::YELLOW . "CAVALADOS API 2.0 — AURIVERDE");
        $sender->sendMessage(TextFormat::GREEN . "MCPE: " . TextFormat::AQUA . "0.14.3 - 0.15.10");
        $sender->sendMessage(TextFormat::GREEN . $this->translate($sender, "cavalados.version.core") . ": " . TextFormat::AQUA . $server->getPocketMineVersion());
        $sender->sendMessage(TextFormat::GREEN . $this->translate($sender, "cavalados.version.api") . ": " . TextFormat::YELLOW . $server->getApiVersion());
        $sender->sendMessage($line);

        return true;
    }

    private function translate(CommandSender $sender, $key, array $params = [])
    {
        return $sender->getServer()->getLanguage()->translateString($key, $params);
    }
}
