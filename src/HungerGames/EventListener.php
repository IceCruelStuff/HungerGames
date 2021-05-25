<?php

namespace HungerGames;

use HungerGames\Lib\Utils\Msg;
use HungerGames\Tasks\WaitingForPlayersTask;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\tile\Sign;

class EventListener implements Listener {

	/** @var Loader */
	private $hungerGamesAPI;

	public function __construct(Loader $loader) {
		$this->hungerGamesAPI = $loader;
	}

	/**
	 * @param PlayerMoveEvent $event
	 */
	public function onMove(PlayerMoveEvent $event) {
		$player = $event->getPlayer();
		$from = clone $event->getFrom();
		$to = $event->getTo();
		if ($this->hungerGamesAPI->getStorage()->isPlayerWaiting($player)) {
			if ($to->x != $from->x || $to->y != $from->y || $to->z != $from->z) {
				$from->yaw = $to->yaw;
				$from->pitch = $to->pitch;
				$event->setTo($from);
			}
		}
	}

	/**
	 * @param SignChangeEvent $event
	 *
	 * @return bool|void
	 *
	 * @throws \InvalidStateException
	 */
	public function onSignChange(SignChangeEvent $event) {
		$player = $event->getPlayer();
		if (!$player->hasPermission("hg.sign.create")) {
			return;
		}
		$tile = $event->getBlock()->level->getTile($event->getBlock());
		if ($tile instanceof Sign) {
			$line1 = $event->getLine(0);
			$line2 = $event->getLine(1);
			if ($line1 === "hg" || $line1 === "[hg]") {
				if (!$this->hungerGamesAPI->getGlobalManager()->exists($line2)) {
					$player->sendMessage(Msg::color("&cGame does not exist..."));
					return;
				}
				$game = $this->hungerGamesAPI->getGlobalManager()->getGameEditorByName($line2);
				$game->addSign($tile);
				$player->sendMessage(Msg::color("&aSuccessfully created HG sign!"));
			}
		}
	}

	/**
	 * @param PlayerInteractEvent $event
	 */
	public function onInteract(PlayerInteractEvent $event) {
		$player = $event->getPlayer();
		$tile = $event->getBlock()->level->getTile($event->getBlock());
		if ($tile instanceof Sign) {
			if ($this->hungerGamesAPI->getSignManager()->isGameSign($tile)) {
				$game = $this->hungerGamesAPI->getSignManager()->getSignGame($tile);
				if ($game === null) {
					return;
				}
				if ($this->hungerGamesAPI->getStorage()->isPlayerSet($player) || $this->hungerGamesAPI->getStorage()->isPlayerWaiting($player)) {
					return;
				}
				if (
					$this->hungerGamesAPI->getGlobalManager()->getGameManager($game)->getStatus() === "running" ||
					$this->hungerGamesAPI->getGlobalManager()->getGameManager($game)->getStatus() === "reset"
				) {
					return;
				}
				$this->hungerGamesAPI->getGlobalManager()->getGameManager($game)->addWaitingPlayer($player, true);
				if ($this->hungerGamesAPI->getGlobalManager()->getGameManager($game)->isWaiting) {
					return; // checks if task started
				}
				$task = new WaitingForPlayersTask($this->hungerGamesAPI, $game);
				$handler = $this->hungerGamesAPI->getScheduler()->scheduleRepeatingTask($task, 20);
				$task->setHandler($handler);
				$this->hungerGamesAPI->getGlobalManager()->getGameManager($game)->isWaiting = true;
			}
		}
	}

	/**
	 * @param EntityLevelChangeEvent $event
	 */
	public function onLevelChange(EntityLevelChangeEvent $event) {
		$player = $event->getEntity();
		if ($player instanceof Player) {
			if ($this->hungerGamesAPI->getStorage()->isPlayerSet($player)) {
				$game = $this->hungerGamesAPI->getStorage()->getPlayerGame($player);
				if ($game !== null) {
					$this->hungerGamesAPI->getGlobalManager()->getGameManager($game)->removePlayer($player, true);
				}
			} elseif ($this->hungerGamesAPI->getStorage()->isPlayerWaiting($player)) {
				$game = $this->hungerGamesAPI->getStorage()->getWaitingPlayerGame($player);
				if ($game !== null) {
					$this->hungerGamesAPI->getGlobalManager()->getGameManager($game)->removeWaitingPlayer($player, true);
				}
			}
		}
	}

	/**
	 * @param PlayerQuitEvent $event
	 */
	public function playerQuitEvent(PlayerQuitEvent $event) {
		$player = $event->getPlayer();
		if ($this->hungerGamesAPI->getStorage()->isPlayerSet($player)) {
			$game = $this->hungerGamesAPI->getStorage()->getPlayerGame($player);
			if ($game !== null) {
				$this->hungerGamesAPI->getGlobalManager()->getGameManager($game)->removePlayer($player, true);
			}
		} elseif ($this->hungerGamesAPI->getStorage()->isPlayerWaiting($player)) {
			$game = $this->hungerGamesAPI->getStorage()->getWaitingPlayerGame($player);
			if ($game !== null) {
				$this->hungerGamesAPI->getGlobalManager()->getGameManager($game)->removeWaitingPlayer($player, true);
			}
		}
	}

	/**
	 * @param PlayerDeathEvent $event
	 */
	public function playerDeathEvent(PlayerDeathEvent $event) {
		$player = $event->getEntity();
		if ($this->hungerGamesAPI->getStorage()->isPlayerSet($player)) {
			$game = $this->hungerGamesAPI->getStorage()->getPlayerGame($player);
			if ($game !== null) {
				$this->hungerGamesAPI->getGlobalManager()->getGameManager($game)->removePlayer($player);
			}
			$count = $this->hungerGamesAPI->getStorage()->getPlayersInGameCount($game);
			if ($count > 1) {
				$msg = Msg::getHungerGamesMessage("hg.message.death");
				$msg = str_replace(["%player%", "%game%", "%left%"], [$player->getName(), $game->getName(), $count], $msg);
				$this->hungerGamesAPI->getGlobalManager()->getGameManager($game)->sendGameMessage(Msg::color($msg));
			}
		}
	}

	/**
	 * @param BlockBreakEvent $event
	 */
	public function onBlockBreak(BlockBreakEvent $event) {
		if ($this->hungerGamesAPI->getStorage()->isPlayerWaiting($event->getPlayer())) {
			$event->setCancelled();
		}
	}

}
