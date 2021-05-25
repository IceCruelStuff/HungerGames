<?php

namespace HungerGames\Lib\Manager;

use HungerGames\Lib\Utils\Msg;
use HungerGames\Loader;
use HungerGames\Object\HungerGames;
use pocketmine\math\Vector3;
use pocketmine\tile\Sign;
use pocketmine\utils\Config;

class SignManager {

    /** @var Loader */
    private $hungerGamesAPI;
    /** @var int */
    private $refreshedSignsCount = 0;
    /** @var Sign[] */
    private $refreshedSigns = [];

    public function __construct(Loader $loader) {
        $this->hungerGamesAPI = $loader;
    }

    /**
     * Gets amount of refreshed signs
     *
     * @return int
     */
    public function getRefreshedSignCount() {
        return $this->refreshedSignsCount;
    }

    /**
     * Sets amount of refreshed signs
     *
     * @param int $amount
     */
    public function setRefreshedSignsCount(int $amount) {
        if (!is_int($amount)) {
            return;
        }
        $this->refreshedSignsCount = $amount;
    }

    /**
     * Gets refreshed signs tiles
     *
     * @return Sign[]
     */
    public function getRefreshedSigns() {
        return $this->refreshedSigns;
    }

    /**
     * clears refreshed signs cache
     */
    public function clearRefreshedSigns() {
        $this->refreshedSigns = [];
    }

    /**
     * Checks if a sign is a game sign
     *
     * @param Sign $tile
     * @return bool
     */
    public function isGameSign(Sign $tile) {
        foreach ($this->hungerGamesAPI->getAllGameArenas() as $arena) {
            $val = "{$tile->x}:{$tile->y}:{$tile->z}:{$tile->level->getName()}";
            if (isset($arena->getAll()["sign_list"][$val])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Gets game sign
     *
     * @param Sign $tile
     * @return HungerGames|null
     */
    public function getSignGame(Sign $tile) {
        foreach ($this->hungerGamesAPI->getGlobalManager()->getGames() as $game) {
            $cf = (new Config($this->hungerGamesAPI->dataPath() . "arenas/{$game->getName()}.yml", Config::YAML))->getAll();
            $val = "{$tile->x}:{$tile->y}:{$tile->z}:{$tile->level->getName()}";
            if (isset($cf["sign_list"][$val])) {
                return $game;
            }
        }
        return null;
    }

    /**
     * Refreshes all game signs
     */
    public function refreshAllSigns() {
        foreach ($this->hungerGamesAPI->getGlobalManager()->getGames() as $game) {
            $this->refreshSigns($game);
        }
    }

    /**
     * @param HungerGames $game
     */
    public function refreshSigns(HungerGames $game) {
        foreach ($game->getSignList() as $tile => $value) {
            $tile = explode(":", $tile);
            if (count($tile) < 4) {
                continue;
            }
            if (!$this->hungerGamesAPI->getServer()->isLevelLoaded($tile[3])) {
                continue;
            }
            $tile = $this->hungerGamesAPI->getServer()->getLevelByName($tile[3])->getTile(new Vector3((int) $tile[0], (int) $tile[1], (int) $tile[2]));
            if (!$tile instanceof Sign) {
                continue;
            }
            $lines = [];
            $lines[0] = $game->getGameArena()->get("sign_line_1");
            $lines[1] = $game->getGameArena()->get("sign_line_2");
            $lines[2] = $game->getGameArena()->get("sign_line_3");
            $lines[3] = $game->getGameArena()->get("sign_line_4");
            $outPut = [];
            foreach ($lines as $line) {
                $on = $this->hungerGamesAPI->getStorage()->getPlayersInGameCount($game) + $this->hungerGamesAPI->getStorage()->getAllWaitingPlayersInGameCount($game);
                $max = $game->getMaximumPlayers();
                $gameName = $game->getName();
                $status = $this->hungerGamesAPI->getGlobalManager()->getGameManager($game)->getStatus();
                $outPut[] = str_replace(
                    [
                        "{on}",
                        "{max}",
                        "{game}",
                        "{status}"
                    ],
                    [
                        $on,
                        $max,
                        $gameName,
                        $status
                    ],
                    $line
                );
            }
            $tile->setText(Msg::color($outPut[0]), Msg::color($outPut[1]), Msg::color($outPut[2]), Msg::color($outPut[3]));
            ++$this->refreshedSignsCount;
            $this->refreshedSigns[] = $tile;
        }
    }

}
