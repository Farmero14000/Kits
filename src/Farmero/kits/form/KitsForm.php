<?php

declare(strict_types=1);

namespace Farmero\kits\form;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

use jojoe77777\FormAPI\SimpleForm;

use Farmero\kits\Kits;

class KitsForm {

    public static function sendKitsForm(Player $player): void {
        $form = new SimpleForm(function (Player $player, ?int $data) {
            if ($data === null) {
                return;
            }
            $kitName = array_keys(Kits::getInstance()->getKitsManager()->getKitsConfig()->getAll())[$data];
            $kitsManager = Kits::getInstance()->getKitsManager();

            if (!$kitsManager->kitExists($kitName)) {
                $player->sendMessage(TextFormat::RED . "The kit $kitName does not exist.");
                return;
            }

            if (!$kitsManager->hasPermissionForKit($player, $kitName)) {
                $player->sendMessage(TextFormat::RED . "You do not have permission to claim the $kitName kit.");
                return;
            }

            if ($kitsManager->isOnCooldown($player, $kitName)) {
                $remainingTime = $kitsManager->getRemainingCooldown($player, $kitName);
                $formattedCooldown = $kitsManager->formatCooldownMessage($remainingTime);
                $player->sendMessage(TextFormat::RED . "You cannot claim the $kitName kit yet. Cooldown remaining: $formattedCooldown.");
                return;
            }

            if ($kitsManager->giveKit($player, $kitName)) {
                $player->sendMessage(TextFormat::GREEN . "You have claimed the $kitName kit!");
            } else {
                $player->sendMessage(TextFormat::RED . "Failed to claim the $kitName kit.");
            }
        });
        $form->setTitle("Kits");
        $form->setContent("Select a kit to claim:");
        $kitsManager = Kits::getInstance()->getKitsManager();
        foreach ($kitsManager->getKitsConfig()->getAll() as $kitName => $kitData) {
            $displayName = $kitsManager->getKitDisplayName($kitName);
            $form->addButton($displayName);
        }
        $player->sendForm($form);
    }
}
