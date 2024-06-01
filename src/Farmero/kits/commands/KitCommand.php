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
        parent::__construct(Kits::getInstance()->getConfig()->get("command_label"));
        $this->setLabel(Kits::getInstance()->getConfig()->get("command_label"));
        $this->setDescription(Kits::getInstance()->getConfig()->get("command_description"));
        $this->setAliases(Kits::getInstance()->getConfig()->get("command_aliases"));
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
                    $sender->sendMessage(TextFormat::GREEN . $displayName);
                }
                return true;
            }
        }

        $kitName = $args[0];
        if (!$kitsManager->kitExists($kitName)) {
            $displayName = $kitsManager->getKitDisplayName($kitName);
            $sender->sendMessage(TextFormat::RED . "The kit $displayName does not exist, Try again...");
            return false;
        }

        if (!$kitsManager->hasPermissionForKit($sender, $kitName)) {
            $displayName = $kitsManager->getKitDisplayName($kitName);
            $sender->sendMessage(TextFormat::RED . "You do not have permission to claim the $displayName kit!");
            return false;
        }

        if ($kitsManager->isOnCooldown($sender, $kitName)) {
            $displayName = $kitsManager->getKitDisplayName($kitName);
            $remainingTime = $kitsManager->getRemainingCooldown($sender, $kitName);
            $formattedCooldown = $kitsManager->formatCooldownMessage($remainingTime);
            $sender->sendMessage(TextFormat::RED . "You cannot claim the $displayName kit yet... Cooldown remaining: $formattedCooldown");
            return false;
        }

        if ($kitsManager->giveKit($sender, $kitName)) {
            $displayName = $kitsManager->getKitDisplayName($kitName);
            $sender->sendMessage(TextFormat::GREEN . "You have claimed the $displayName kit!");
        } else {
            $displayName = $kitsManager->getKitDisplayName($kitName);
            $sender->sendMessage(TextFormat::RED . "Failed to claim the $displayName kit...");
        }
        return true;
    }
}
