<?php

namespace HungerGames\Lib;

use HungerGames\Loader;
use HungerGames\Object\HungerGames;
use pocketmine\Player;

class GameStorage extends Storage {

    /**
     * Loads all games
     * @param HungerGames $game
     */
    public function loadGame(HungerGames $game) {
        $this->players[$game->getName()] = [];
        $this->waitingPlayers[$game->getName()] = [];
    }

    /**
     * Searches for game in game list
     *
     * @param HungerGames $game
     * @return bool
     */
    public function searchGame(HungerGames $game) {
        return isset($this->players[$game->getName()]);
    }

    /**
     * Add a game player
     *
     * @param Player      $player
     * @param HungerGames $game
     */
    public function addPlayer(Player $player, HungerGames $game) {
        if ($this->searchGame($game)) {
            $this->players[$game->getName()][] = $player;
        }
    }

    /**
     * Gets game of player playing a game
     *
     * @param Player $player
     * @return HungerGames|null
     */
    public function getPlayerGame(Player $player) {
        foreach ($this->players as $game => $players) {
            foreach ($players as $pl) {
                if ($pl === $player) {
                    return Loader::getInstance()->getGlobalManager()->getGameByName($game);
                }
            }
        }
        return null;
    }

    /**
     * Removes a game player
     *
     * @param Player $player
     */
    public function removePlayer(Player $player) {
        foreach ($this->players as $no => $game) {
            foreach ($game as $i => $j) {
                if ($j === $player) {
                    unset($this->players[$no][$i]);
                }
            }
        }
    }

    /**
     * Removes all players
     */
    public function removeAllPlayers() {
        foreach ($this->getAllPlayers() as $player) {
            $this->removePlayer($player);
        }
    }

    /**
     * Returns all players in a game
     *
     * @param HungerGames $game
     * @return null|Player[]
     */
    public function getPlayersInGame(HungerGames $game) {
        $players = [];
        if ($this->searchGame($game)) {
            foreach ($this->players[$game->getName()] as $o => $player) {
                if ($player instanceof Player) {
                    $players[] = $player;
                }
            }
        }
        return $players !== null ? $players : null;
    }

    /**
     * Returns count of all players in a game
     *
     * @param HungerGames $game
     * @return int
     */
    public function getPlayersInGameCount(HungerGames $game) {
        return count($this->getPlayersInGame($game));
    }

    /**
     * Checks if player is set in players
     *
     * @param Player $player
     * @return bool
     */
    public function isPlayerSet(Player $player) {
        foreach ($this->players as $n => $game) {
            foreach ($game as $i => $j) {
                if ($j === $player) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Returns array of players in play
     *
     * @return Player[]
     */
    public function getAllPlayers() {
        $players = [];
        foreach ($this->players as $res => $ret) {
            foreach ($ret as $sal => $tar) {
                $players[] = $tar;
            }
        }
        return $players;
    }

    /**
     * Returns count of all players
     *
     * @return int
     */
    public function getAllPlayersCount() {
        return count($this->getAllPlayers());
    }

    /**
     * Removes all players from game
     *
     * @param HungerGames $game
     */
    public function removePlayersInGame(HungerGames $game) {
        foreach ($this->getPlayersInGame($game) as $player) {
            $this->removePlayer($player);
        }
    }

    /**
     * Searches if there are game players waiting
     *
     * @param HungerGames $game
     * @return bool
     */
    public function searchAwaitingGame(HungerGames $game) {
        return isset($this->waitingPlayers[$game->getName()]);
    }

    /**
     * Adds a waiting player to array
     *
     * @param Player      $player
     * @param HungerGames $game
     */
    public function addWaitingPlayer(Player $player, HungerGames $game) {
        if ($this->searchAwaitingGame($game)) {
            $this->waitingPlayers[$game->getName()][] = $player;
        }
    }

    /**
     * Gets game of waiting player playing a game
     *
     * @param Player $player
     * @return HungerGames|null
     */
    public function getWaitingPlayerGame(Player $player) {
        foreach ($this->waitingPlayers as $game => $players) {
            foreach ($players as $pl) {
                if ($pl === $player) {
                    return Loader::getInstance()->getGlobalManager()->getGameByName($game);
                }
            }
        }
        return null;
    }

    /**
     * Removes a waiting players from array
     *
     * @param Player $player
     */
    public function removeWaitingPlayer(Player $player) {
        foreach ($this->waitingPlayers as $no => $game) {
            foreach ($game as $i => $j) {
                if ($j === $player) {
                    unset($this->waitingPlayers[$no][$i]);
                }
            }
        }
    }

    /**
     * Removes all waiting players
     */
    public function removeAllWaitingPlayers() {
        foreach ($this->getAllWaitingPlayers() as $player) {
            $this->removeWaitingPlayer($player);
        }
    }

    /**
     * Returns all players in a waiting game
     *
     * @param HungerGames $game
     * @return null|Player[]
     */
    public function getPlayersInWaitingGame(HungerGames $game) {
        $players = [];
        if ($this->searchGame($game)) {
            foreach ($this->waitingPlayers[$game->getName()] as $o => $player) {
                if ($player instanceof Player) {
                    $players[] = $player;
                }
            }
        }
        return $players !== null ? $players : null;
    }

    /**
     * Returns count of all waiting players in a game
     *
     * @param HungerGames $game
     * @return int
     */
    public function getAllWaitingPlayersInGameCount(HungerGames $game) {
        return count($this->getPlayersInWaitingGame($game));
    }

    /**
     * Removes all waiting players from game
     *
     * @param HungerGames $game
     */
    public function removePlayersInWaitingGame(HungerGames $game) {
        foreach ($this->getPlayersInGame($game) as $player) {
            $this->removeWaitingPlayer($player);
        }
    }

    /**
     * Returns array of players waiting
     *
     * @return Player[]
     */
    public function getAllWaitingPlayers() {
        $players = [];
        foreach ($this->waitingPlayers as $res => $ret) {
            foreach ($ret as $sal => $tar) {
                $players[] = $tar;
            }
        }
        return $players;
    }

    /**
     * Returns count of all waiting players
     *
     * @return int
     */
    public function getAllWaitingPlayersCount() {
        return count($this->getAllWaitingPlayers());
    }

    /**
     * Checks if player is set in waiting players
     *
     * @param Player $player
     * @return bool
     */
    public function isPlayerWaiting(Player $player) {
        foreach ($this->waitingPlayers as $n => $game) {
            foreach ($game as $i => $j) {
                if ($j === $player) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @deprecated this function is no longer in use and will be removed in future versions
     *
     * @return bool
     */
    public function scanOverload() {
        return false;
    }

}
