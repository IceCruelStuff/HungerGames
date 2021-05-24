<?php

namespace HungerGames\API\Scripts;

use HungerGames\Lib\Utils\Exc;
use HungerGames\Loader;
use HungerGames\Object\HungerGames;
use pocketmine\Player;

class HungerGamesAPIManager {

    /** @var HungerGamesAPI[] */
    protected $scripts = [];
    /** @var Loader */
    private $hungerGamesAPI;

    public function __construct(Loader $main) {
        $this->hungerGamesAPI = $main;
    }

    /**
     * Loads script
     *
     * @param HungerGamesAPI $script
     * @return bool
     */
    public function loadScript($script) {
        if ($script instanceof HungerGamesAPI) {
            $script->onLoad();
            $this->scripts[$script->getName()] = $script;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Loads all scripts
     */
    public function loadScripts() {
        foreach (glob($this->hungerGamesAPI->dataPath() . "scripts/*.php", GLOB_BRACE) as $f) {
            /** @noinspection PhpIncludeInspection */
            include_once $f;
            foreach (Exc::getFileClasses($f) as $class) {
                $class = new $class();
                if (!$class instanceof HungerGamesAPI) {
                    continue;
                }
                if (isset($this->scripts[$class->getName()])) {
                    continue;
                }
                $this->loadScript($class);
            }
        }
    }

    /**
     * Reloads all scripts
     */
    public function reloadScripts() {
        $this->scripts = [];
        $this->loadScripts();
    }

    /**
     * returns script by name
     *
     * @param $name
     * @return HungerGamesAPI|null
     */
    public function getScript($name) {
        if (isset($this->scripts[$name])) {
            return $this->scripts[$name];
        }
        return null;
    }

    /**
     * returns all loaded scripts
     *
     * @return HungerGamesAPI[]
     */
    public function getScripts() {
        return $this->scripts;
    }

    /**
     * @param Player      $player
     * @param HungerGames $game
     */
    public function callOnPlayerJoinGame(Player $player, HungerGames $game) {
        foreach ($this->getScripts() as $script) {
            if ($script->isEnabled()) {
                $script->onPlayerJoinGame($player, $game);
            }
        }
    }

    /**
     * @param Player      $player
     * @param HungerGames $game
     */
    public function callOnPlayerQuitGame(Player $player, HungerGames $game) {
        foreach ($this->getScripts() as $script) {
            if ($script->isEnabled()) {
                $script->onPlayerQuitGame($player, $game);
            }
        }
    }

    /**
     * @param Player      $player
     * @param HungerGames $game
     */
    public function callGameIsFull(Player $player, HungerGames $game) {
        foreach ($this->getScripts() as $script) {
            if ($script->isEnabled()) {
                $script->gameIsFull($player, $game);
            }
        }
    }

    /**
     * @param array       $players
     * @param HungerGames $game
     */
    public function callWhileWaitingForPlayers(array $players, HungerGames $game) {
        foreach ($this->getScripts() as $script) {
            if ($script->isEnabled()) {
                $script->whileWaitingForPlayers($players, $game);
            }
        }
    }

    /**
     * @param array       $players
     * @param HungerGames $game
     */
    public function callWhileWaitingToStart(array $players, HungerGames $game) {
        foreach ($this->getScripts() as $script) {
            if ($script->isEnabled()) {
                $script->whileWaitingForPlayers($players, $game);
            }
        }
    }

    /**
     * @param array       $players
     * @param HungerGames $game
     */
    public function callOnGameStart(array $players, HungerGames $game) {
        foreach ($this->getScripts() as $script) {
            if ($script->isEnabled()) {
                $script->onGameStart($players, $game);
            }
        }
    }

    /**
     * @param array       $players
     * @param HungerGames $game
     */
    public function callOnDeathMatchStart(array $players, HungerGames $game) {
        foreach ($this->getScripts() as $script) {
            if ($script->isEnabled()) {
                $script->onDeathMatchStart($players, $game);
            }
        }
    }

    /**
     * @param Player      $player
     * @param HungerGames $game
     */
    public function callOnPlayerWinGame(Player $player, HungerGames $game) {
        foreach ($this->getScripts() as $script) {
            if ($script->isEnabled()) {
                $script->onPlayerWinGame($player, $game);
            }
        }
    }

    /**
     * @param array       $players
     * @param HungerGames $game
     */
    public function callOnGameEnd(array $players, HungerGames $game) {
        foreach ($this->getScripts() as $script) {
            if ($script->isEnabled()) {
                $script->onGameEnd($players, $game);
            }
        }
    }

}
