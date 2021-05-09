<?php

namespace hungergames\lib\mgr;

use hungergames\lib\utils\Msg;
use hungergames\Loader;
use hungergames\obj\HungerGames;
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
	private $HGApi;
	/** @var Position[] */
	private $openSlots = [];
	/** @var Position[] */
	private $usedSlots = [];
	/** @var bool */
	public $isWaiting = false;

	public function __construct(HungerGames $game, Loader $main) {
		$this->HGApi = $main;
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
	 * @param $new
	 */
	public function setStatus($new) {
		$this->status = $new;
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
		if (isset($this->usedSlots[strtolower($player->getName()) ])) {
			$this->addOpenSlots([$this->usedSlots[strtolower($player->getName()) ]]);
			unset($this->usedSlots[strtolower($player->getName()) ]);
		}
	}

	/**
	 *
	 * Teleport player to game position
	 *
	 * @param Player $p
	 *
	 * @return bool
	 *
	 */
	public function tpPlayerToOpenSlot(Player $p) {
		if ($this->getOpenSlotCount() < 1) return false;
		$slot = array_pop($this->openSlots);
		$this->addUsedSlots([strtolower($p->getName()) => $slot]);
		$p->teleport($slot);
		return true;
	}

	/**
	 *
	 * Sends game players message
	 *
	 * @param $message
	 *
	 */
	public function sendGameMessage($message) {
		$pig = $this->HGApi->getStorage()->getPlayersInGame($this->getGame());
		for ($i = 0;$i < count($pig);++$i) {
			$pig[$i]->sendMessage($message);
		}
		$piWg = $this->HGApi->getStorage()->getPlayersInWaitingGame($this->getGame());
		for ($i = 0;$i < count($piWg);++$i) {
			$piWg[$i]->sendMessage($message);
		}
	}

	/**
	 *
	 * Sends game players tip
	 *
	 * @param $message
	 *
	 */
	public function sendGameTip($message) {
		$pig = $this->HGApi->getStorage()->getPlayersInGame($this->getGame());
		for ($i = 0;$i < count($pig);++$i) {
			$pig[$i]->sendTip($message);
		}
		$piWg = $this->HGApi->getStorage()->getPlayersInWaitingGame($this->getGame());
		for ($i = 0;$i < count($piWg);++$i) {
			$piWg[$i]->sendTip($message);
		}
	}

	/**
	 *
	 * Sends game players popup
	 *
	 * @param $message
	 *
	 */
	public function sendGamePopup($message) {
		$pig = $this->HGApi->getStorage()->getPlayersInGame($this->getGame());
		for ($i = 0;$i < count($pig);++$i) {
			$pig[$i]->sendPopup($message);
		}
		$piWg = $this->HGApi->getStorage()->getPlayersInWaitingGame($this->getGame());
		for ($i = 0;$i < count($piWg);++$i) {
			$piWg[$i]->sendPopup($message);
		}
	}

	/**
	 *
	 * Adds player into game
	 *
	 * @param Player $p
	 * @param bool   $message
	 *
	 */
	public function addPlayer(Player $p, $message = false) {
		if (!$this->tpPlayerToOpenSlot($p)) {
			foreach ($this->HGApi->getScriptManager()->getScripts() as $script) {
				if (!$script->isEnabled()) continue;
				$script->gameIsFull($p, $this->getGame());
			}
			if ($message) {
				$p->sendMessage(Msg::color(str_replace(["%player%", "%game%"], [$p->getName(), $this->getGame()->getName() ], Msg::getHGMessage("hg.message.full"))));
			}
			return;
		}
		if ($this->game->clearInventoryOnJoin()) {
			$p->getInventory()->clearAll();
			$p->getArmorInventory()->clearAll();
		}
		$this->HGApi->getStorage()->addPlayer($p, $this->getGame());
		foreach ($this->HGApi->getScriptManager()->getScripts() as $script) {
			if (!$script->isEnabled()) continue;
			$script->onPlayerJoinGame($p, $this->getGame());
		}
		if ($message) {
			$this->sendGameMessage(Msg::color(str_replace(["%player%", "%game%"], [$p->getName(), $this->getGame()->getName() ], Msg::getHGMessage("hg.message.join"))));
		}
	}

	/**
	 *
	 * Removes player from game
	 *
	 * @param Player $p
	 * @param bool   $message
	 *
	 */
	public function removePlayer(Player $p, $message = false) {
		$this->resetUsedSlot($p);
		$this->HGApi->getStorage()->removePlayer($p);
		$p->teleport($this->getGame()->getLobbyPosition());
		foreach ($this->HGApi->getScriptManager()->getScripts() as $script) {
			if (!$script->isEnabled()) continue;
			$script->onPlayerQuitGame($p, $this->getGame());
		}
		if ($message) {
			$this->sendGameMessage(Msg::color(str_replace(["%player%", "%game%"], [$p->getName(), $this->getGame()->getName() ], Msg::getHGMessage("hg.message.quit"))));
		}
	}

	/**
	 *
	 * Removes player from game without teleporting
	 *
	 * @param Player $p
	 * @param bool   $message
	 *
	 */
	public function removePlayerWithoutTeleport(Player $p, $message = false) {
		$this->HGApi->getStorage()->removePlayer($p);
		foreach ($this->HGApi->getScriptManager()->getScripts() as $script) {
			if (!$script->isEnabled()) continue;
			$script->onPlayerQuitGame($p, $this->getGame());
		}
		if ($message) {
			$this->sendGameMessage(Msg::color(str_replace(["%player%", "%game%"], [$p->getName(), $this->getGame()->getName() ], Msg::getHGMessage("hg.message.quit"))));
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
		foreach ($players as $p) {
			if ($p instanceof Player) {
				$this->addPlayer($p, $message);
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
		foreach ($this->HGApi->getStorage()->getPlayersInGame($this->getGame()) as $p) {
			$this->removePlayer($p, $message);
		}
		foreach ($players as $p) {
			if ($p instanceof Player) {
				$this->addPlayer($p, $message);
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
	 * @param Player $p
	 * @param bool   $message
	 *
	 */
	public function addWaitingPlayer(Player $p, $message = false) {
		if (!$this->tpPlayerToOpenSlot($p)) {
			foreach ($this->HGApi->getScriptManager()->getScripts() as $script) {
				if (!$script->isEnabled()) continue;
				$script->gameIsFull($p, $this->getGame());
			}
			if ($message) {
				$p->sendMessage(Msg::color(str_replace(["%player%", "%game%"], [$p->getName(), $this->getGame()->getName() ], Msg::getHGMessage("hg.message.full"))));
			}
			return;
		}
		if ($this->game->clearInventoryOnJoin()) {
			$p->getInventory()->clearAll();
		}
		$this->HGApi->getStorage()->addWaitingPlayer($p, $this->getGame());
		foreach ($this->HGApi->getScriptManager()->getScripts() as $script) {
			if (!$script->isEnabled()) continue;
			$script->onPlayerJoinGame($p, $this->getGame());
		}
		if ($message) {
			$this->sendGameMessage(Msg::color(str_replace(["%player%", "%game%"], [$p->getName(), $this->getGame()->getName() ], Msg::getHGMessage("hg.message.join"))));
		}
	}

	/**
	 *
	 * Removes player from game
	 *
	 * @param Player $p
	 * @param bool   $message
	 *
	 */
	public function removeWaitingPlayer(Player $p, $message = false) {
		$this->resetUsedSlot($p);
		$this->HGApi->getStorage()->removeWaitingPlayer($p);
		$p->teleport($this->game->getLobbyPosition());
		foreach ($this->HGApi->getScriptManager()->getScripts() as $script) {
			if (!$script->isEnabled()) continue;
			$script->onPlayerQuitGame($p, $this->getGame());
		}
		if ($message) {
			$this->sendGameMessage(Msg::color(str_replace(["%player%", "%game%"], [$p->getName(), $this->getGame()->getName() ], Msg::getHGMessage("hg.message.quit"))));
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
		foreach ($this->HGApi->getStorage()->getAllWaitingPlayers() as $p) {
			$this->removeWaitingPlayer($p, $message);
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
		foreach ($players as $p) {
			if ($p instanceof Player) {
				$this->addWaitingPlayer($p, $message);
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
		foreach ($this->HGApi->getStorage()->getPlayersInWaitingGame($this->getGame()) as $p) {
			$this->removeWaitingPlayer($p, $message);
		}
		foreach ($players as $p) {
			if ($p instanceof Player) {
				$this->addWaitingPlayer($p, $message);
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
