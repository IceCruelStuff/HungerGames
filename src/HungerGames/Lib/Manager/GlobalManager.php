<?php

namespace HungerGames\Lib\Manager;

use HungerGames\Lib\Editor\GameEditor;
use HungerGames\Loader;
use HungerGames\Object\HungerGames;
use pocketmine\utils\TextFormat;

class GlobalManager {

    /** @var HungerGames[] */
    private $games = [];
    /** @var GameManager[] */
    private $gamesManager = [];
    /** @var GameEditor[] */
    private $gamesEditor = [];
    /** @var Loader */
    private $hungerGamesAPI;

    public function __construct(Loader $loader) {
        $this->hungerGamesAPI = $loader;
    }

    /**
     * Loads game into global manager
     *
     * @param HungerGames $game
     */
    public function load(HungerGames $game) {
        $game->init();
        $this->games[$game->getName()] = $game;
        $game->loadGame($game);
        $this->hungerGamesAPI->getStorage()->loadGame($game);
        $this->gamesManager[$game->getName()] = new GameManager($game, $this->hungerGamesAPI);
        $this->gamesEditor[$game->getName()] = new GameEditor($game);
        $game->createGameLevelBackup();
        $this->hungerGamesAPI->getLogger()->info(TextFormat::GREEN . "Creating map backup for game '" . TextFormat::YELLOW . $game->getName() . TextFormat::GREEN . "'");
    }

    /**
     * Checks if game exists
     *
     * @param string $name
     * @return bool
     */
    public function exists(string $name) {
        return isset($this->games[$name]);
    }

    /**
     * Remove game from global manager
     *
     * @param HungerGames $game
     */
    public function remove(HungerGames $game) {
        if (isset($this->games[$game->getName()])) {
            unset($this->games[$game->getName()]);
        }
        if (isset($this->gamesManager[$game->getName()])) {
            unset($this->gamesManager[$game->getName()]);
        }
        if (isset($this->gamesEditor[$game->getName()])) {
            unset($this->gamesEditor[$game->getName()]);
        }
    }

    /**
     * Get game by name from global manager
     *
     * @param string $name
     * @return HungerGames|null
     */
    public function getGameByName(string $name) {
        if(isset($this->games[$name])){
            return $this->games[$name];
        }
        return null;
    }

    /**
     * Gets game manager by hg object from global manager
     *
     * @param HungerGames $game
     * @return GameManager|null
     */
    public function getGameManager(HungerGames $game) {
        if (isset($this->gamesManager[$game->getName()])) {
            return $this->gamesManager[$game->getName()];
        }
        return null;
    }

    /**
     * Get game manager by name from global manager
     *
     * @param string $name
     * @return GameManager|null
     */
    public function getGameManagerByName(string $name) {
        if (isset($this->gamesManager[$name])) {
            return $this->gamesManager[$name];
        }
        return null;
    }

    /**
     * Gets game editor by hg object from global manager
     *
     * @param HungerGames $game
     * @return null
     */
    public function getGameEditor(HungerGames $game) {
        if (isset($this->gamesEditor[$game->getName()])) {
            return $this->gamesEditor[$game->getName()];
        }
        return null;
    }

    /**
     * Get game manager by name from global manager
     *
     * @param string $name
     * @return GameEditor|null
     */
    public function getGameEditorByName(string $name) {
        if(isset($this->gamesEditor[$name])){
            return $this->gamesEditor[$name];
        }
        return null;
    }

    /**
     * Gets all loaded games
     *
     * @return HungerGames[]
     */
    public function getGames() {
        return $this->games;
    }

    /**
     * Gets all game managers
     *
     * @return GameManager[]
     */
    public function getGameManagers() {
        return $this->gamesManager;
    }

    /**
     * Gets all game editors
     *
     * @return GameEditor[]
     */
    public function getGameEditors() {
        return $this->gamesEditor;
    }

}
