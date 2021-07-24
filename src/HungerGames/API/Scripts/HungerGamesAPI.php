<?php

namespace HungerGames\API\Scripts;

use HungerGames\Loader;
use HungerGames\Object\HungerGames;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\utils\MainLogger;

abstract class HungerGamesAPI {

    /** @var string */
    public $scriptName;
    /** @var string */
    public $version;
    /** @var string */
    public $author;
    /** @var MainLogger */
    private $logger;
    /** @var bool */
    protected $enabled = true;
    /** @var string */
    private $scriptConfigPath;
    /** @var Config */
    protected $config;

    public function __construct($name, $version = "1.0", $author = "") {
        $this->scriptName = $name;
        $this->version = $version;
        $this->author = $author;
        $this->logger = MainLogger::getLogger();
        $this->scriptConfigPath = Loader::getInstance()->dataPath() . "scriptConfigs/";
    }

    /**
     * Creates script config
     *
     * @param string $name
     * @param array $values
     * @return Config
     */
    public function createConfig(string $name, array $values) {
        if (substr($name, strlen($name) - 4) !== ".yml") {
            $this->config = new Config($this->scriptConfigPath . $name . ".yml", Config::YAML, $values);
        } else {
            $this->config = new Config($this->scriptConfigPath . $name, Config::YAML, $values);
        }
        return $this->config;
    }

    /**
     * Gets script config
     *
     * @return Config
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * Gets the name of the script
     *
     * @return string
     */
    public function getName() {
        return $this->scriptName;
    }

    /**
     * Gets the name of the script
     *
     * @return string
     */
    public function getVersion() {
        return $this->version;
    }

    /**
     * Gets the author of the script
     *
     * @return string
     */
    public function getAuthor() {
        return $this->author;
    }

    /**
     * Disables script
     */
    public function setDisabled() {
        $this->enabled = false;
    }

    /**
     * Enables script
     */
    public function setEnabled() {
        $this->enabled = true;
    }

    /**
     * returns whether script is enabled or not
     *
     * @return bool
     */
    public function isEnabled() {
        return $this->enabled;
    }

    /**
     * Sends console message
     *
     * @param string $message
     */
    public function sendConsoleMessage(string $message) {
        $this->logger->notice("[HungerGames Script: " . $this->getName() . "]: " . $message);
    }

    /**
     * Called when script is loaded
     */
    public function onLoad() {

    }

    /**
     * called when player joins game
     *
     * @param Player      $player
     * @param HungerGames $game
     */
    public function onPlayerJoinGame(Player $player, HungerGames $game) {

    }

    /**
     * called when player quits game
     *
     * @param Player      $player
     * @param HungerGames $game
     */
    public function onPlayerQuitGame(Player $player, HungerGames $game) {

    }

    /**
     * Called when player fails to join full game
     *
     * @param Player      $player
     * @param HungerGames $game
     */
    public function gameIsFull(Player $player, HungerGames $game) {

    }

    /**
     * Called when player is waiting for players
     *
     * @param array       $players
     * @param HungerGames $game
     */
    public function whileWaitingForPlayers(array $players, HungerGames $game) {

    }

    /**
     * Called when player is waiting for players
     *
     * @param array       $players
     * @param HungerGames $game
     */
    public function whileWaitingToStart(array $players, HungerGames $game) {

    }

    /**
     * Called when game starts
     *
     * @param array       $players
     * @param HungerGames $game
     */
    public function onGameStart(array $players, HungerGames $game) {

    }

    /**
     * Called when death match starts
     *
     * @param array       $players
     * @param HungerGames $game
     */
    public function onDeathMatchStart(array $players, HungerGames $game) {

    }

    /**
     * Called when players wins a game
     *
     * @param Player      $player
     * @param HungerGames $game
     */
    public function onPlayerWinGame(Player $player, HungerGames $game) {

    }

    /**
     * Called when players lose a game
     *
     * @param Player      $player
     * @param HungerGames $game
     */
    public function onPlayerLoseGame(Player $player, HungerGames $game) {

    }

    /**
     * Called when game ends with no winner
     *
     * @param array       $players
     * @param HungerGames $game
     */
    public function onGameEnd(array $players, HungerGames $game) {

    }

}
