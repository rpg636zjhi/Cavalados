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
use pocketmine\network\protocol\Info;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;

use function count;
use function implode;
use function stripos;
use function strtolower;

use const PHP_VERSION;

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

        if (count($args) === 0) {
            $server = $sender->getServer();
            $line = TextFormat::DARK_GREEN . "========================================";
            $sender->sendMessage($line);
            $sender->sendMessage(TextFormat::GREEN . "          " . TextFormat::YELLOW . $this->translate($sender, "cavalados.version.title"));
            $sender->sendMessage(TextFormat::GREEN . $this->translate($sender, "cavalados.version.server") . ": " . TextFormat::YELLOW . $server->getName());
            $sender->sendMessage(TextFormat::GREEN . $this->translate($sender, "cavalados.version.core") . ": " . TextFormat::AQUA . $server->getPocketMineVersion() . TextFormat::GOLD . " (" . $server->getCodename() . ")");
            $sender->sendMessage(TextFormat::GREEN . $this->translate($sender, "cavalados.version.api") . ": " . TextFormat::YELLOW . $server->getApiVersion());
            $sender->sendMessage(TextFormat::GREEN . $this->translate($sender, "cavalados.version.minecraft") . ": " . TextFormat::AQUA . $server->getVersion() . TextFormat::GOLD . " | " . $this->translate($sender, "cavalados.version.protocol") . ": " . Info::CURRENT_PROTOCOL);
            $sender->sendMessage(TextFormat::GREEN . $this->translate($sender, "cavalados.version.php") . ": " . TextFormat::YELLOW . PHP_VERSION);
            $sender->sendMessage(TextFormat::GREEN . $this->translate($sender, "cavalados.version.plugins") . ": " . TextFormat::AQUA . count($server->getPluginManager()->getPlugins()));
            $sender->sendMessage(TextFormat::AQUA . $this->translate($sender, "cavalados.version.hint"));
            $sender->sendMessage($line);
        } else {
            $pluginName = implode(" ", $args);
            $exactPlugin = $sender->getServer()->getPluginManager()->getPlugin($pluginName);

            if ($exactPlugin instanceof Plugin) {
                $this->describeToSender($exactPlugin, $sender);
                return true;
            }

            $found = false;
            $pluginName = strtolower($pluginName);
            foreach ($sender->getServer()->getPluginManager()->getPlugins() as $plugin) {
                if (stripos($plugin->getName(), $pluginName) !== false) {
                    $this->describeToSender($plugin, $sender);
                    $found = true;
                }
            }

            if (!$found) {
                $sender->sendMessage(TextFormat::YELLOW . $this->translate($sender, "pocketmine.command.version.noSuchPlugin"));
            }
        }

        return true;
    }

    private function describeToSender(Plugin $plugin, CommandSender $sender)
    {
        $desc = $plugin->getDescription();
        $sender->sendMessage(TextFormat::DARK_GREEN . "======== " . TextFormat::YELLOW . $desc->getName() . TextFormat::DARK_GREEN . " ========");
        $sender->sendMessage(TextFormat::GREEN . $this->translate($sender, "cavalados.version.pluginVersion") . ": " . TextFormat::AQUA . $desc->getVersion());

        if ($desc->getDescription() !== null) {
            $sender->sendMessage(TextFormat::YELLOW . $desc->getDescription());
        }
        if ($desc->getWebsite() !== null) {
            $sender->sendMessage(TextFormat::GREEN . $this->translate($sender, "cavalados.version.website") . ": " . TextFormat::AQUA . $desc->getWebsite());
        }

        $authors = $desc->getAuthors();
        if (count($authors) > 0) {
            $label = count($authors) === 1 ? "cavalados.version.author" : "cavalados.version.authors";
            $sender->sendMessage(TextFormat::GREEN . $this->translate($sender, $label) . ": " . TextFormat::YELLOW . implode(", ", $authors));
        }
    }

    private function translate(CommandSender $sender, $key, array $params = [])
    {
        return $sender->getServer()->getLanguage()->translateString($key, $params);
    }
}
