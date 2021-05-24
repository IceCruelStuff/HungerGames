<?php

namespace HungerGames\Object;

use HungerGames\Lib\Utils\Exc;
use HungerGames\Loader;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\utils\Config;

class HungerGames extends Game {

    /** @var int */
    public $min;
    /** @var int */
    public $max;
    /** @var float */
    public $gameSeconds;
    /** @var float */
    public $waitingSeconds;
    /** @var Level */
    public $gameLevel;
    /** @var Position */
    public $lobbyPos;
    /** @var Position */
    public $deathMatchPos;
    /** @var Position[] */
    private $slots;
    /** @var bool */
    private $isSkyWars;
    /** @var bool */
    private $clearInventoryOnJoin;
    /** @var Item[] */
    private $chestItems;
    /** @var float */
    private $refillAfter;
    /** @var \string[] */
    private $signList;
    /** @var bool */
    private $init = false;
    /** @var Config */
    private $game;

    /**
     *
     * Initiates game data
     *
     */
    public function init() {
        $this->game = Loader::getInstance()->getGameArenaByName($this->getName());
        $this->reloadMinimumPlayers();
        $this->reloadMaximumPlayers();
        $this->reloadGameSeconds();
        $this->reloadWaitingSeconds();
        $this->reloadGameLevel();
        $this->reloadDeathMatchPosition();
        $this->reloadLobbyPosition();
        $this->reloadSlots();
        $this->reloadIsSkyWars();
        $this->reloadClearInventoryOnJoin();
        $this->reloadChestItems();
        $this->reloadRefillAfter();
        $this->reloadSignList();
        $this->init = true;
    }

    /**
     *
     * get the game configuration arena
     *
     * @return Config
     *
     */
    public function getGameArena() : Config {
        return $this->game;
    }

    /**
     *
     * Reloads the game arena configuration
     *
     */
    public function reloadGameArena() {
        $this->game->reload();
    }

    /**
     *
     * Checks if game is initiated
     *
     * @return bool
     *
     */
    public function isHGInitiated() {
        return $this->init !== false;
    }

    /**
     *
     * Minimum amount of players of game
     *
     * @return int
     *
     */
    public function getMinimumPlayers() : int {
        return $this->min;
    }

    /**
     *
     * Reloads minimum amount of players of game
     *
     */
    public function reloadMinimumPlayers() {
        $this->min = Exc::stringToInteger($this->game->get("min_players"));
    }

    /**
     *
     * Maximum amount of players of game
     *
     * @return int
     *
     */
    public function getMaximumPlayers() : int {
        return $this->max;
    }

    /**
     *
     * Reloads maximum amount of players of game
     *
     */
    public function reloadMaximumPlayers() {
        $this->max = Exc::stringToInteger($this->game->get("max_players"));
    }

    /**
     *
     * Game seconds of game
     *
     * @return float
     *
     */
    public function getGameSeconds() : float {
        return $this->gameSeconds;
    }

    /**
     *
     * Reloads game seconds of game
     *
     */
    public function reloadGameSeconds() {
        $this->gameSeconds = Exc::stringToFloat($this->game->get("game_seconds"));
    }

    /**
     *
     * Waiting seconds of game
     *
     * @return float
     *
     */
    public function getWaitingSeconds() : float {
        return $this->waitingSeconds;
    }

    /**
     *
     * Reloads waiting seconds of game
     *
     */
    public function reloadWaitingSeconds() {
        $this->waitingSeconds = Exc::stringToFloat($this->game->get("waiting_seconds"));
    }

    /**
     *
     * Level of game
     *
     * @return Level
     *
     */
    public function getGameLevel() : Level {
        return $this->gameLevel;
    }

    /**
     *
     * Deletes old map backups
     *
     */
    public function deleteOldMapBackup() {
        $oldMap = Loader::getInstance()->dataPath() . "mapBackups/" . $this->gameLevel->getFolderName();
        Exc::delete($oldMap);
    }

    /**
     *
     * Creates a backup of current game
     *
     */
    public function createGameLevelBackup() {
        $server = Loader::getInstance()->getServer();
        if ($server->isLevelLoaded($this->gameLevel->getName())) {
            $server->unloadLevel($this->gameLevel);
        }
        $levelSrc = $server->getDataPath() . "worlds/" . $this->gameLevel->getFolderName();
        $levelDst = Loader::getInstance()->dataPath() . "mapBackups/" . $this->gameLevel->getFolderName();
        Loader::getInstance()->getMapBackup()->asyncWrite($levelSrc, $levelDst, $this->getName());
    }

    /**
     *
     * Creates a backup of current game
     *
     */
    public function resetGameLevelBackup() {
        $server = Loader::getInstance()->getServer();
        if ($server->isLevelLoaded($this->gameLevel->getName())) {
            $server->unloadLevel($this->gameLevel);
        }
        $levelSrc = Loader::getInstance()->dataPath() . "mapBackups/" . $this->gameLevel->getFolderName();
        $levelDst = $server->getDataPath() . "worlds/" . $this->gameLevel->getFolderName();
        Loader::getInstance()->getMapBackup()->asyncWrite($levelSrc, $levelDst, $this->getName());
    }

    /**
     *
     * Reloads game level
     *
     */
    public function reloadGameLevel() {
        if (!Loader::getInstance()->getServer()->isLevelLoaded($this->game->get("game_level"))) {
            Loader::getInstance()->getServer()->loadLevel($this->game->get("game_level"));
        }
        $this->gameLevel = Loader::getInstance()->getServer()->getLevelByName($this->game->get("game_level"));
        if ($this->gameLevel === null) {
            Loader::getInstance()->getLogger()->alert("Game level of game " . $this->getName() . " not found, using default level");
            $this->gameLevel = Loader::getInstance()->getServer()->getDefaultLevel();
        }
    }

    /**
     *
     * Position of game lobby
     *
     * @return Position
     *
     */
    public function getLobbyPosition() : Position {
        return $this->lobbyPos;
    }

    /**
     *
     * Reloads game lobby position
     *
     */
    public function reloadLobbyPosition() {
        $lobby_pos = $this->game->get("lobby_pos");
        if (!Loader::getInstance()->getServer()->isLevelLoaded($lobby_pos["level"])) {
            Loader::getInstance()->getServer()->loadLevel($lobby_pos["level"]);
        }
        $lobby_level = Loader::getInstance()->getServer()->getLevelByName($lobby_pos["level"]);
        if ($lobby_level === null) {
            Loader::getInstance()->getLogger()->alert("Level of death match for game " . $this->getName() . " not found, using default level");
            $lobby_level = Loader::getInstance()->getServer()->getDefaultLevel();
        }
        $this->lobbyPos = new Position(floatval($lobby_pos["x"]), floatval($lobby_pos["y"]), floatval($lobby_pos["z"]), $lobby_level);
    }

    /**
     *
     * Position of game death match
     *
     * @return Position
     *
     */
    public function getDeathMatchPosition() : Position {
        return $this->deathMatchPos;
    }

    /**
     *
     * Reloads game death match position
     *
     */
    public function reloadDeathMatchPosition() {
        $dm_pos = $this->game->get("death_match_pos");
        if (!Loader::getInstance()->getServer()->isLevelLoaded($dm_pos["level"])) {
            Loader::getInstance()->getServer()->loadLevel($dm_pos["level"]);
        }
        $dm_level = Loader::getInstance()->getServer()->getLevelByName($dm_pos["level"]);
        if ($dm_level === null) {
            Loader::getInstance()->getLogger()->alert("Level of death match for game " . $this->getName() . " not found, using default level");
            $dm_level = Loader::getInstance()->getServer()->getDefaultLevel();
        }
        $this->deathMatchPos = new Position(floatval($dm_pos["x"]), floatval($dm_pos["y"]), floatval($dm_pos["z"]), $dm_level);
    }

    /**
     *
     * Returns all slots of games
     *
     * @return Position[]|null
     *
     */
    public function getSlots() : array {
        $slots = [];
        foreach ($this->slots as $slotNumber => $pos) {
            $slots[] = new Position(floatval($pos["x"]), floatval($pos["y"]), floatval($pos["z"]), $this->gameLevel);
        }
        return $slots === null ? null : $slots;
    }

    /**
     *
     * Reloads slots
     *
     */
    public function reloadSlots() {
        $this->slots = $this->game->get("slots");
    }

    /**
     *
     * Returns if game is SkyWars
     *
     * @return string
     *
     */
    public function isSkyWars() : string {
        return strtolower($this->isSkyWars);
    }

    /**
     *
     * Reloads if game is SkyWars
     *
     */
    public function reloadIsSkyWars() {
        $this->isSkyWars = $this->game->get("is_sky_wars");
    }

    /**
     *
     * Returns if game should clear players inventory on join
     *
     * @return bool
     *
     */
    public function clearInventoryOnJoin() : bool {
        return $this->clearInventoryOnJoin;
    }

    /**
     *
     * Reloads if game should clear players inventory on join
     *
     */
    public function reloadClearInventoryOnJoin() {
        $this->clearInventoryOnJoin = boolval($this->game->get("clear_inventory_on_join", true));
    }

    /**
     *
     * Returns after how much time the chests are refilled
     *
     * @return float
     *
     */
    public function refillAfter() : float {
        return $this->refillAfter;
    }

    /**
     *
     * Reloads after how much time the chests are refilled
     *
     */
    public function reloadRefillAfter() {
        $this->refillAfter = $this->game->get("refill_chests_after_seconds");
    }

    /**
     *
     * Returns all chest items
     *
     * @return Item[]
     *
     */
    public function getChestItems() : array {
        $items = [];
        foreach ($this->chestItems as $item) {
            $item = explode(" ", $item);
            if (count($item) < 3) {
                $items[] = Item::get(0, 0, 1);
            }
            $random = mt_rand(1, 2);
            if ($random > 1) {
                $items[] = Item::get(0, 0, 1);
            } else {
                $items[] = Item::get($item[0], $item[1], $item[2]);
            }
        }
        return $items;
    }

    /**
     *
     * Reloads the chest items
     *
     */
    public function reloadChestItems() {
        $this->chestItems = $this->game->get("chest_items");
    }

    /**
     *
     * Gets signs list
     *
     * @return \string[]
     *
     */
    public function getSignList() : array {
        return $this->signList;
    }

    /**
     *
     * Reloads sign list
     *
     */
    public function reloadSignList() {
        $this->signList = $this->game->get("sign_list");
    }

}
