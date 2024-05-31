<?php

declare(strict_types=1);

namespace Farmero\kits;

use pocketmine\player\Player;
use pocketmine\item\StringToItemParser;
use pocketmine\utils\Config;
use pocketmine\item\Item;
use pocketmine\scheduler\Task;

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

    public function giveKit(Player $player, string $kitName): bool {
        $kit = $this->getKit($kitName);
        if ($kit === null) {
            return false;
        }

        $cooldownSeconds = $kit["cooldown"];
        if ($cooldownSeconds > 0 && $this->isOnCooldown($player, $kitName)) {
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
                    $item->setLore($itemData["lore"]);
                }
                $player->getInventory()->addItem($item);
            }
        }

        if ($cooldownSeconds > 0) {
            $this->setCooldown($player, $kitName, $cooldownSeconds);
        }

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