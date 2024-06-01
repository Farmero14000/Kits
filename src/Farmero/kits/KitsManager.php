<?php

declare(strict_types=1);

namespace Farmero\kits;

use pocketmine\player\Player;
use pocketmine\item\StringToItemParser;
use pocketmine\utils\Config;

use Farmero\kits\task\KitCooldownTask;

use Farmero\kits\Kits;

class KitsManager {

    private Config $kitsConfig;
    private array $cooldowns = [];
    private string $cooldownFile;

    public function __construct() {
        $this->kitsConfig = new Config(Kits::getInstance()->getDataFolder() . "kits.yml", Config::YAML);
        $this->cooldownFile = Kits::getInstance()->getDataFolder() . "kit_cooldowns.json";
        $this->loadCooldowns();
        Kits::getInstance()->getScheduler()->scheduleRepeatingTask(new KitCooldownTask(), 20);
    }

    public function getKit(string $kitName): ?array {
        $kitData = $this->kitsConfig->get($kitName);
        if ($kitData !== null) {
            $kitData["cooldown"] = isset($kitData["cooldown"]) ? (int)$kitData["cooldown"] : 0;
        }
        return $kitData;
    }

    public function kitExists(string $kitName): bool {
        return $this->kitsConfig->exists($kitName);
    }

    public function giveKit(Player $player, string $kitName): bool {
        $kit = $this->getKit($kitName);
        if ($kit === null) {
            return false;
        }

        $currentTime = time();
        $cooldownSeconds = $kit["cooldown"];
        if ($cooldownSeconds > 0 && $this->isOnCooldown($player, $kitName)) {
            $cooldownMessage = $this->formatCooldownMessage($this->getRemainingCooldown($player, $kitName));
            $player->sendMessage("Failed to claim $kitName. Cooldown remaining: $cooldownMessage");
            return false;
        }

        foreach ($kit["items"] as $itemData) {
            $item = StringToItemParser::getInstance()->parse($itemData["id"]);
            if ($item !== null) {
                $item->setCount($itemData["count"]);
                if (isset($itemData["name"])) {
                    $item->setCustomName($itemData["name"]);
                }
                if (isset($itemData["lore"])) {
                    $item->setLore(explode("\n", $itemData["lore"]));
                }
                $player->getInventory()->addItem($item);
            }
        }

        if (isset($kit["armor"])) {
            $armorInventory = $player->getArmorInventory();
            foreach ($kit["armor"] as $armorPiece => $armorData) {
                $armorItem = StringToItemParser::getInstance()->parse($armorData["id"]);
                if ($armorItem !== null) {
                    if (isset($armorData["name"])) {
                        $armorItem->setCustomName($armorData["name"]);
                    }
                    switch ($armorPiece) {
                        case "helmet":
                            if ($armorInventory->getHelmet()->isNull()) {
                                $armorInventory->setHelmet($armorItem);
                            } else {
                                $player->getInventory()->addItem($armorItem);
                            }
                            break;
                        case "chestplate":
                            if ($armorInventory->getChestplate()->isNull()) {
                                $armorInventory->setChestplate($armorItem);
                            } else {
                                $player->getInventory()->addItem($armorItem);
                            }
                            break;
                        case "leggings":
                            if ($armorInventory->getLeggings()->isNull()) {
                                $armorInventory->setLeggings($armorItem);
                            } else {
                                $player->getInventory()->addItem($armorItem);
                            }
                            break;
                        case "boots":
                            if ($armorInventory->getBoots()->isNull()) {
                                $armorInventory->setBoots($armorItem);
                            } else {
                                $player->getInventory()->addItem($armorItem);
                            }
                            break;
                    }
                }
            }
        }

        if ($cooldownSeconds > 0) {
            $this->setCooldown($player, $kitName, $cooldownSeconds);
        }

        $player->sendMessage("You have received the $kitName kit!");
        return true;
    }

    public function isOnCooldown(Player $player, string $kitName): bool {
        $currentTime = time();
        if (isset($this->cooldowns[$player->getName()][$kitName])) {
            $cooldownTime = $this->cooldowns[$player->getName()][$kitName];
            if ($currentTime < $cooldownTime) {
                return true;
            }
        }
        return false;
    }

    public function getRemainingCooldown(Player $player, string $kitName): int {
        $currentTime = time();
        $cooldownTime = $this->cooldowns[$player->getName()][$kitName];
        return max(0, $cooldownTime - $currentTime);
    }

    public function formatCooldownMessage(int $seconds): string {
        $days = floor($seconds / 86400);
        $seconds -= $days * 86400;
        $hours = floor($seconds / 3600);
        $seconds -= $hours * 3600;
        $minutes = floor($seconds / 60);
        $seconds -= $minutes * 60;

        $formatted = '';
        if ($days > 0) {
            $formatted .= "$days days ";
        }
        if ($hours > 0) {
            $formatted .= "$hours hours ";
        }
        if ($minutes > 0) {
            $formatted .= "$minutes minutes ";
        }
        $formatted .= "$seconds seconds";

        return $formatted;
    }

    public function setCooldown(Player $player, string $kitName, int $cooldownSeconds): void {
        $currentTime = time();
        $cooldownTime = $currentTime + $cooldownSeconds;
        $this->cooldowns[$player->getName()][$kitName] = $cooldownTime;
        $this->saveCooldowns();
    }

    public function removeCooldown(string $playerName, string $kitName): void {
        if (isset($this->cooldowns[$playerName][$kitName])) {
            unset($this->cooldowns[$playerName][$kitName]);
            $this->saveCooldowns();
        }
    }

    public function getCooldowns(): array {
        return $this->cooldowns;
    }

    public function getKitsConfig(): Config {
        return $this->kitsConfig;
    }

    private function loadCooldowns(): void {
        if (file_exists($this->cooldownFile)) {
            $data = file_get_contents($this->cooldownFile);
            $this->cooldowns = json_decode($data, true);
        }
    }

    private function saveCooldowns(): void {
        $data = json_encode($this->cooldowns, JSON_PRETTY_PRINT);
        file_put_contents($this->cooldownFile, $data);
    }
}
