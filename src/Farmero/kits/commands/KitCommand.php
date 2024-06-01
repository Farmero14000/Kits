<?php

declare(strict_types=1);

namespace Farmero\kits\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

use Farmero\kits\Kits;

use Farmero\kits\form\KitsForm;

class KitCommand extends Command {

    public function __construct() {
        parent::__construct("kit", "Claim a kit", "/kit [name]", ["kits"]);
        $this->setPermission("kits.cmd.kit");
    }

    public function execute(CommandSender $sender, string $label, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "This command can only be used in-game.");
            return false;
        }

        if (!$this->testPermission($sender)) {
            return false;
        }

        $kitsManager = Kits::getInstance()->getKitsManager();

        if (count($args) === 0) {
            if (Kits::getInstance()->isUseUI()) {
                KitsForm::sendKitsForm($sender);
                return true;
            } else {
                $sender->sendMessage(TextFormat::YELLOW . "Available kits:");
                foreach ($kitsManager->getKitsConfig()->getAll() as $kitName => $kitData) {
                    $displayName = $kitsManager->getKitDisplayName($kitName);
                    $sender->sendMessage(TextFormat::GREEN . "- " . $displayName);
                }
                return true;
            }
        }

        $kitName = $args[0];

        if (!$kitsManager->kitExists($kitName)) {
            $sender->sendMessage(TextFormat::RED . "Kit not found.");
            return false;
        }

        $displayName = $kitsManager->getKitDisplayName($kitName);

        if ($kitsManager->giveKit($sender, $kitName)) {
            $sender->sendMessage(TextFormat::GREEN . "You have received the $displayName kit!");
        } else {
            $cooldownMessage = $kitsManager->formatCooldownMessage($kitsManager->getRemainingCooldown($sender, $kitName));
            $sender->sendMessage(TextFormat::RED . "Failed to claim $displayName kit. Cooldown remaining: $cooldownMessage");
        }
        return true;
    }
}
