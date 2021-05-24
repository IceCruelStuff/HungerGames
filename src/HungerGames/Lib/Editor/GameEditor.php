<?php

namespace HungerGames\Lib\Editor;

use HungerGames\Lib\Utils\Exc;
use HungerGames\Object\HungerGames;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\tile\Sign;
use pocketmine\utils\Config;

class GameEditor {

    /** @var HungerGames */
    private $game;
    /** @var Config */
    private $gameArena;

    public function __construct(HungerGames $game) {
        $this->gameArena = $game->getGameArena();
        $this->game = $game;
    }

    /**
     *
     * Sets number of minimum players
     *
     * @param int $min
     *
     */
    public function setMinimumPlayers($min) {
        if (!Exc::checkIsNumber($min)) {
            return;
        }
        $this->push("min_players", (int) $min);
        $this->game->reloadMinimumPlayers();
    }

    /**
     *
     * Sets number of maximum players
     *
     * @param int $max
     *
     */
    public function setMaximumPlayers($max) {
        if (!Exc::checkIsNumber($max)) {
            return;
        }
        $this->push("max_players", (int) $max);
        $this->game->reloadMaximumPlayers();
    }

    /**
     *
     * Sets number of game seconds
     *
     * @param float $seconds
     *
     */
    public function setGameSeconds($seconds) {
        if(!Exc::checkIsNumber($seconds)) {
            return;
        }
        $this->push("game_seconds", (float) $seconds);
        $this->game->reloadGameSeconds();
    }

    /**
     *
     * Sets number of waiting seconds
     *
     * @param float $seconds
     *
     */
    public function setWaitingSeconds($seconds) {
        if (!Exc::checkIsNumber($seconds)) {
            return;
        }
        $this->push("waiting_seconds", (float) $seconds);
        $this->game->reloadWaitingSeconds();
    }

    /**
     *
     * Sets game level
     *
     * @param string $level
     *
     */
    public function setGameLevel($level) {
        $this->push("game_level", $level);
        $this->game->deleteOldMapBackup();
        $this->game->reloadGameLevel();
        $this->game->createGameLevelBackup();
    }

    /**
     *
     * Sets lobby position
     *
     * @param Position $pos
     *
     */
    public function setLobbyPosition(Position $pos) {
        $this->push("lobby_pos", [
            "level" => $pos->level->getName(),
            "x" => floatval($pos->x),
            "y" => floatval($pos->y),
            "z" => floatval($pos->z)
        ]);
        $this->game->reloadLobbyPosition();
    }

    /**
     *
     * Sets death match position
     *
     * @param Position $pos
     *
     */
    public function setDeathMatchPosition(Position $pos) {
        $this->push("death_match_pos", [
            "level" => $pos->level->getName(),
            "x" => floatval($pos->x),
            "y" => floatval($pos->y),
            "z" => floatval($pos->z)
        ]);
        $this->game->reloadDeathMatchPosition();
    }

    /**
     *
     * Adds a new slot by name
     *
     * @param Vector3 $pos
     * @param string  $slotName
     *
     */
    public function addSlot(Vector3 $pos, $slotName) {
        $this->push("slots . $slotName", [
            "x" => $pos->x,
            "y" => $pos->y,
            "z" => $pos->z
        ]);
        $this->game->reloadSlots();
    }

    /**
     *
     * Removes slot by name
     *
     * @param string $slotName
     * @return bool
     *
     */
    public function removeSlot($slotName) {
        if (empty($this->gameArena->getAll()["slots"][$slotName])) {
            return false;
        }
        unset($this->gameArena->getAll()["slots"][$slotName]);
        $this->gameArena->save();
        $this->game->reloadSlots();
        return true;
    }

    /**
     *
     * Updates slot by name
     *
     * @param Vector3 $pos
     * @param string  $slotName
     *
     */
    public function updateSlot(Vector3 $pos, $slotName) {
        $this->removeSlot($slotName);
        $this->addSlot($pos, $slotName);
        $this->game->reloadSlots();
    }

    /**
     *
     * Sets sign lines
     *
     * @param array $params
     *
     */
    public function setSignLines(array $params) {
        for ($i = 0; $i < 4; ++$i) {
            $j = $i + 1;
            $this->push("sign_line_{$j}", $params[$i]);
        }
    }

    /**
     *
     * Adds sign to sign list
     *
     * @param Sign $tile
     *
     */
    public function addSign(Sign $tile) {
        $val = $this->gameArena->getAll();
        $val["sign_list"][$tile->x . ":" . $tile->y . ":" . $tile->z . ":" . $tile->level->getName()] = "";
        $this->gameArena->setAll($val);
        $this->gameArena->save();
        $this->gameArena->reload();
        $this->game->reloadSignList();
    }

    /**
     *
     * Removes sign from sign list
     *
     * @param Sign $tile
     *
     */
    public function removeSign(Sign $tile) {
        $val = $tile->x . ":" . $tile->y . ":" . $tile->z . ":" . $tile->level->getName();
        if (isset($this->gameArena->getAll()["sign_list"][$val])) {
            unset($this->gameArena->getAll()["sign_list"][$val]);
        }
        $this->gameArena->save();
        $this->gameArena->reload();
        $this->game->reloadSignList();
    }

    /**
     *
     * Pushes value into config
     *
     * @param string       $index
     * @param string|array $values
     *
     */
    public function push($index, $values) {
        $this->gameArena->setNested($index, $values);
        $this->gameArena->save();
        $this->gameArena->reload();
    }

}
