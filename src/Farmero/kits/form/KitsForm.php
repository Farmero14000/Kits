<?php

declare(strict_types=1);

namespace Farmero\kits\form;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\ModalForm;

use Farmero\kits\Kits;

class KitsForm {

    public static function sendKitsForm(Player $player): void {
        $form = new SimpleForm(function (Player $player, ?int $data) {
            if ($data === null) {
                return;
            }
            $kitName = array_keys(Kits::getInstance()->getKitsManager()->getKitsConfig()->getAll())[$data];
            self::sendKitConfirmationForm($player, $kitName);
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

    public static function sendKitConfirmationForm(Player $player, string $kitName): void {
        $kitsManager = Kits::getInstance()->getKitsManager();
        if (!$kitsManager->kitExists($kitName)) {
            $player->sendMessage(TextFormat::RED . "The kit $kitName does not exist...");
            return;
        }

        if (!$kitsManager->hasPermissionForKit($player, $kitName)) {
            $displayName = $kitsManager->getKitDisplayName($kitName);
            $player->sendMessage(TextFormat::RED . "You do not have permission to claim the $displayName kit.");
            return;
        }

        $cooldownMessage = '';
        if ($kitsManager->isOnCooldown($player, $kitName)) {
            $remainingTime = $kitsManager->getRemainingCooldown($player, $kitName);
            $cooldownMessage = " Cooldown remaining: " . $kitsManager->formatCooldownMessage($remainingTime);
        } else {
            $kit = $kitsManager->getKit($kitName);
            if (isset($kit["cooldown"]) && $kit["cooldown"] > 0) {
                $cooldownMessage = " Cooldown: " . $kitsManager->formatCooldownMessage($kit["cooldown"]) . " after claiming!";
            }
        }

        $form = new ModalForm(function (Player $player, ?bool $data) use ($kitName) {
            if ($data === null || $data === false) {
                return;
            }

            $kitsManager = Kits::getInstance()->getKitsManager();

            if (!$kitsManager->kitExists($kitName)) {
                $player->sendMessage(TextFormat::RED . "The kit $kitName does not exist...");
                return;
            }

            if (!$kitsManager->hasPermissionForKit($player, $kitName)) {
                $displayName = $kitsManager->getKitDisplayName($kitName);
                $player->sendMessage(TextFormat::RED . "You do not have permission to claim the $displayName kit!");
                return;
            }

            if ($kitsManager->isOnCooldown($player, $kitName)) {
                $remainingTime = $kitsManager->getRemainingCooldown($player, $kitName);
                $formattedCooldown = $kitsManager->formatCooldownMessage($remainingTime);
                $displayName = $kitsManager->getKitDisplayName($kitName);
                $player->sendMessage(TextFormat::RED . "You cannot claim the $displayName kit yet, Cooldown remaining: $formattedCooldown");
                return;
            }

            if ($kitsManager->giveKit($player, $kitName)) {
                $displayName = $kitsManager->getKitDisplayName($kitName);
                $player->sendMessage(TextFormat::GREEN . "You have claimed the $displayName kit!");
            } else {
                $player->sendMessage(TextFormat::RED . "Failed to claim the $kitName kit.");
            }
        });
        $form->setTitle("Confirm");
        $form->setContent("Do you want to claim the $kitName kit? \n $cooldownMessage");
        $form->setButton1("Yes");
        $form->setButton2("No");
        $player->sendForm($form);
    }
}
