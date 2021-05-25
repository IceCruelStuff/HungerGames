<?php

namespace HungerGames\Lib\Manager;

use HungerGames\Lib\Utils\Msg;
use HungerGames\Loader;
use HungerGames\Object\HungerGames;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\tile\Chest;

class GameManager {

    /** @var int */
    public static $runningGames = 0;
    /** @var string */
    public $status;
    /** @var HungerGames */
    public $game;
    /** @var Loader */
    private $hungerGamesAPI;
    /** @var Position[] */
    private $openSlots = [];
    /** @var Position[] */
    private $usedSlots = [];
    /** @var bool */
    public $isWaiting = false;

    public function __construct(HungerGames $game, Loader $loader) {
        $this->hungerGamesAPI = $loader;
        $this->game = $game;
        $this->refresh();
    }

    /**
     * Gets loaded game
     *
     * @return HungerGames
     */
    public function getGame() {
        return $this->game;
    }

    /**
     * Refreshes game to default
     */
    public function refresh() {
        $this->status = "open";
        $this->openSlots = $this->game->getSlots();
        $this->usedSlots = [];
        $this->isWaiting = false;
    }

    /**
     * Gets the status of the game
     *
     * @return string
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Sets the status of the game
     *
     * @param string $status
     */
    public function setStatus(string $status) {
        $this->status = $status;
    }

    /**
     *
     * @return array|null|Position[]
     *
     */
    public function getOpenSlots() {
        return $this->openSlots;
    }

    /**
     *
     * @param array $slots
     *
     */
    public function addOpenSlots(array $slots) {
        foreach ($slots as $slot) {
            if ($slot instanceof Position) {
                $this->openSlots[] = $slot;
            }
        }
    }

    /**
     *
     * @param array $slots
     *
     */
    public function setOpenSlots(array $slots) {
        $this->openSlots = [];
        $this->addOpenSlots($slots);
    }

    /**
     *
     * Checks how much opens slots there are
     *
     * @return int
     *
     */
    public function getOpenSlotCount() {
        return count($this->openSlots);
    }

    /**
     *
     * @return Position[]
     *
     */
    public function getUsedSlots() {
        return $this->usedSlots;
    }

    /**
     *
     * @param array $slots
     *
     */
    public function addUsedSlots(array $slots) {
        foreach ($slots as $key => $slot) {
            if ($slot instanceof Position) {
                $this->usedSlots[$key] = $slot;
            }
        }
    }

    /**
     *
     * @param array $slots
     *
     */
    public function setUsedSlots(array $slots) {
        $this->usedSlots = [];
        $this->addUsedSlots($slots);
    }

    /**
     *
     * Checks how much opens slots there are
     *
     * @return int
     *
     */
    public function getUsedSlotCount() {
        return count($this->usedSlots);
    }

    /**
     *
     * @param Player $player
     *
     */
    public function resetUsedSlot(Player $player) {
        if (isset($this->usedSlots[strtolower($player->getName())])) {
            $this->addOpenSlots([$this->usedSlots[strtolower($player->getName())]]);
            unset($this->usedSlots[strtolower($player->getName())]);
        }
    }

    /**
     *
     * Teleport player to game position
     *
     * @param Player $player
     *
     * @return bool
     *
     */
    public function tpPlayerToOpenSlot(Player $player) {
        if ($this->getOpenSlotCount() < 1) {
            return false;
        }
        $slot = array_pop($this->openSlots);
        $this->addUsedSlots([strtolower($player->getName()) => $slot]);
        $player->teleport($slot);
        return true;
    }

    /**
     *
     * Sends game players message
     *
     * @param string $message
     *
     */
    public function sendGameMessage(string $message) {
        $pig = $this->hungerGamesAPI->getStorage()->getPlayersInGame($this->getGame());
        for ($i = 0; $i < count($pig); ++$i) {
            $pig[$i]->sendMessage($message);
        }
        $piWg = $this->hungerGamesAPI->getStorage()->getPlayersInWaitingGame($this->getGame());
        for ($i = 0; $i < count($piWg); ++$i) {
            $piWg[$i]->sendMessage($message);
        }
    }

    /**
     *
     * Sends game players tip
     *
     * @param string $message
     *
     */
    public function sendGameTip(string $message) {
        $pig = $this->hungerGamesAPI->getStorage()->getPlayersInGame($this->getGame());
        for ($i = 0; $i < count($pig); ++$i) {
            $pig[$i]->sendTip($message);
        }
        $piWg = $this->hungerGamesAPI->getStorage()->getPlayersInWaitingGame($this->getGame());
        for ($i = 0; $i < count($piWg); ++$i) {
            $piWg[$i]->sendTip($message);
        }
    }

    /**
     *
     * Sends game players popup
     *
     * @param string $message
     *
     */
    public function sendGamePopup(string $message) {
        $pig = $this->hungerGamesAPI->getStorage()->getPlayersInGame($this->getGame());
        for ($i = 0; $i < count($pig); ++$i) {
            $pig[$i]->sendPopup($message);
        }
        $piWg = $this->hungerGamesAPI->getStorage()->getPlayersInWaitingGame($this->getGame());
        for ($i = 0; $i < count($piWg); ++$i) {
            $piWg[$i]->sendPopup($message);
        }
    }

    /**
     *
     * Adds player into game
     *
     * @param Player $player
     * @param bool   $message
     *
     */
    public function addPlayer(Player $player, $message = false) {
        if (!$this->tpPlayerToOpenSlot($player)) {
            foreach ($this->hungerGamesAPI->getScriptManager()->getScripts() as $script) {
                if (!$script->isEnabled()) {
                    continue;
                }
                $script->gameIsFull($player, $this->getGame());
            }
            if ($message) {
                $player->sendMessage(Msg::color(str_replace(["%player%", "%game%"], [$player->getName(), $this->getGame()->getName()], Msg::getHungerGamesMessage("hg.message.full"))));
            }
            return;
        }
        if ($this->game->clearInventoryOnJoin()) {
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
        }
        $this->hungerGamesAPI->getStorage()->addPlayer($player, $this->getGame());
        foreach ($this->hungerGamesAPI->getScriptManager()->getScripts() as $script) {
            if (!$script->isEnabled()) {
                continue;
            }
            $script->onPlayerJoinGame($player, $this->getGame());
        }
        if ($message) {
            $this->sendGameMessage(Msg::color(str_replace(["%player%", "%game%"], [$player->getName(), $this->getGame()->getName()], Msg::getHungerGamesMessage("hg.message.join"))));
        }
    }

    /**
     *
     * Removes player from game
     *
     * @param Player $player
     * @param bool   $message
     *
     */
    public function removePlayer(Player $player, $message = false) {
        $this->resetUsedSlot($player);
        $this->hungerGamesAPI->getStorage()->removePlayer($player);
        $player->teleport($this->getGame()->getLobbyPosition());
        foreach ($this->hungerGamesAPI->getScriptManager()->getScripts() as $script) {
            if (!$script->isEnabled()) {
                continue;
            }
            $script->onPlayerQuitGame($player, $this->getGame());
        }
        if ($message) {
            $this->sendGameMessage(Msg::color(str_replace(["%player%", "%game%"], [$player->getName(), $this->getGame()->getName()], Msg::getHungerGamesMessage("hg.message.quit"))));
        }
    }

    /**
     *
     * Removes player from game without teleporting
     *
     * @param Player $player
     * @param bool   $message
     *
     */
    public function removePlayerWithoutTeleport(Player $player, $message = false) {
        $this->hungerGamesAPI->getStorage()->removePlayer($player);
        foreach ($this->hungerGamesAPI->getScriptManager()->getScripts() as $script) {
            if (!$script->isEnabled()) {
                continue;
            }
            $script->onPlayerQuitGame($player, $this->getGame());
        }
        if ($message) {
            $this->sendGameMessage(Msg::color(str_replace(["%player%", "%game%"], [$player->getName(), $this->getGame()->getName()], Msg::getHungerGamesMessage("hg.message.quit"))));
        }
    }

    /**
     *
     * Adds player into game
     *
     * @param array $players
     * @param bool  $message
     *
     */
    public function addPlayers(array $players, $message = false) {
        foreach ($players as $player){
            if ($player instanceof Player) {
                $this->addPlayer($player, $message);
            }
        }
    }

    /**
     *
     * Sets players of game
     *
     * @param array $players
     * @param bool  $message
     *
     */
    public function setPlayers(array $players, $message = false) {
        foreach ($this->hungerGamesAPI->getStorage()->getPlayersInGame($this->getGame()) as $player) {
            $this->removePlayer($player, $message);
        }
        foreach ($players as $player) {
            if ($player instanceof Player) {
                $this->addPlayer($player, $message);
            }
        }
    }

    /**
     *
     * Replaces all waiting players
     *
     * @param Player $newPlayer
     * @param Player $oldPlayer
     * @param bool   $message
     *
     */
    public function replacePlayer(Player $newPlayer, Player $oldPlayer, $message = false) {
        $this->removePlayer($oldPlayer, $message);
        $this->addPlayer($newPlayer, $message);
    }

    /**
     *
     * Adds player into game
     *
     * @param Player $player
     * @param bool   $message
     *
     */
    public function addWaitingPlayer(Player $player, $message = false) {
        if (!$this->tpPlayerToOpenSlot($player)) {
            foreach ($this->hungerGamesAPI->getScriptManager()->getScripts() as $script) {
                if (!$script->isEnabled()) {
                    continue;
                }
                $script->gameIsFull($player, $this->getGame());
            }
            if ($message) {
                $player->sendMessage(Msg::color(str_replace(["%player%", "%game%"], [$player->getName(), $this->getGame()->getName()], Msg::getHungerGamesMessage("hg.message.full"))));
            }
            return;
        }
        if ($this->game->clearInventoryOnJoin()) {
            $player->getInventory()->clearAll();
        }
        $this->hungerGamesAPI->getStorage()->addWaitingPlayer($player, $this->getGame());
        foreach ($this->hungerGamesAPI->getScriptManager()->getScripts() as $script) {
            if (!$script->isEnabled()) {
                continue;
            }
            $script->onPlayerJoinGame($player, $this->getGame());
        }
        if ($message) {
            $this->sendGameMessage(Msg::color(str_replace(["%player%", "%game%"], [$player->getName(), $this->getGame()->getName()], Msg::getHungerGamesMessage("hg.message.join"))));
        }
    }

    /**
     *
     * Removes player from game
     *
     * @param Player $player
     * @param bool   $message
     *
     */
    public function removeWaitingPlayer(Player $player, $message = false) {
        $this->resetUsedSlot($player);
        $this->hungerGamesAPI->getStorage()->removeWaitingPlayer($player);
        $player->teleport($this->game->getLobbyPosition());
        foreach ($this->hungerGamesAPI->getScriptManager()->getScripts() as $script) {
            if (!$script->isEnabled()) {
                continue;
            }
            $script->onPlayerQuitGame($player, $this->getGame());
        }
        if ($message) {
            $this->sendGameMessage(Msg::color(str_replace(["%player%", "%game%"], [$player->getName(), $this->getGame()->getName()], Msg::getHungerGamesMessage("hg.message.quit"))));
        }
    }

    /**
     *
     * removes all waiting players
     *
     * @param bool|false $message
     *
     */
    public function removeWaitingPlayers($message = false) {
        foreach ($this->hungerGamesAPI->getStorage()->getAllWaitingPlayers() as $player) {
            $this->removeWaitingPlayer($player, $message);
        }
    }

    /**
     *
     * Adds waiting player into game
     *
     * @param array $players
     * @param bool  $message
     *
     */
    public function addWaitingPlayers(array $players, $message = false) {
        foreach ($players as $player) {
            if ($player instanceof Player) {
                $this->addWaitingPlayer($player, $message);
            }
        }
    }

    /**
     *
     * Sets waiting players of game
     *
     * @param array $players
     * @param bool  $message
     *
     */
    public function setWaitingPlayers(array $players, $message = false) {
        foreach ($this->hungerGamesAPI->getStorage()->getPlayersInWaitingGame($this->getGame()) as $player) {
            $this->removeWaitingPlayer($player, $message);
        }
        foreach ($players as $player) {
            if ($player instanceof Player) {
                $this->addWaitingPlayer($player, $message);
            }
        }
    }

    /**
     *
     * Swaps players
     *
     * @param Player $newPlayer
     * @param Player $oldPlayer
     * @param bool   $message
     *
     */
    public function replaceWaitingPlayer(Player $newPlayer, Player $oldPlayer, $message = false) {
        $this->removeWaitingPlayer($oldPlayer, $message);
        $this->addWaitingPlayer($newPlayer, $message);
    }

    /**
     *
     * Refills chests
     *
     */
    public function refillChests() {
        foreach ($this->game->gameLevel->getTiles() as $tile) {
            if ($tile instanceof Chest) {
                $tile->getInventory()->setContents($this->game->getChestItems());
            }
        }
    }

}
