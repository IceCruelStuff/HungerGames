<?php

namespace hungergames;

use hungergames\lib\utils\Msg;
use hungergames\tasks\WaitingForPlayersTask;
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
	 * @param PlayerMoveEvent $e
	 */
	public function onMove(PlayerMoveEvent $e) {
		$p = $e->getPlayer();
		$from = clone $e->getFrom();
		$to = $e->getTo();
		if ($this->hungerGamesAPI->getStorage()->isPlayerWaiting($p)) {
			if ($to->x != $from->x || $to->y != $from->y || $to->z != $from->z) {
				$from->yaw = $to->yaw;
				$from->pitch = $to->pitch;
				$e->setTo($from);
			}
		}
	}

	/**
	 * @param SignChangeEvent $e
	 *
	 * @return bool|void
	 *
	 * @throws \InvalidStateException
	 */
	public function onSignChange(SignChangeEvent $e) {
		$p = $e->getPlayer();
		if (!$p->hasPermission("hg.sign.create")) {
			return;
		}
		$b = $e->getBlock()->level->getTile($e->getBlock());
		if ($b instanceof Sign) {
			$line1 = $e->getLine(0);
			$line2 = $e->getLine(1);
			if ($line1 === "hg" || $line1 === "[hg]") {
				if (!$this->hungerGamesAPI->getGlobalManager()->exists($line2)) {
					$p->sendMessage(Msg::color("&cGame does not exist..."));
					return;
				}
				$game = $this->hungerGamesAPI->getGlobalManager()->getGameEditorByName($line2);
				$game->addSign($b);
				$p->sendMessage(Msg::color("&aSuccessfully created HG sign!"));
			}
		}
	}

	/**
	 * @param PlayerInteractEvent $e
	 */
	public function onInteract(PlayerInteractEvent $e) {
		$p = $e->getPlayer();
		$b = $e->getBlock()->level->getTile($e->getBlock());
		if ($b instanceof Sign) {
			if ($this->hungerGamesAPI->getSignManager()->isGameSign($b)) {
				$game = $this->hungerGamesAPI->getSignManager()->getSignGame($b);
				if ($game === null) {
					return;
				}
				if ($this->hungerGamesAPI->getStorage()->isPlayerSet($p) || $this->hungerGamesAPI->getStorage()->isPlayerWaiting($p)) {
					return;
				}
				if (
					$this->hungerGamesAPI->getGlobalManager()->getGameManager($game)->getStatus() === "running" ||
					$this->hungerGamesAPI->getGlobalManager()->getGameManager($game)->getStatus() === "reset"
				) {
					return;
				}
				$this->hungerGamesAPI->getGlobalManager()->getGameManager($game)->addWaitingPlayer($p, true);
				if ($this->hungerGamesAPI->getGlobalManager()->getGameManager($game)->isWaiting) {
					return; // checks if task started
				}
				$t = new WaitingForPlayersTask($this->hungerGamesAPI, $game);
				$h = $this->hungerGamesAPI->getScheduler()->scheduleRepeatingTask($t, 20);
				$t->setHandler($h);
				$this->hungerGamesAPI->getGlobalManager()->getGameManager($game)->isWaiting = true;
			}
		}
	}

	/**
	 * @param EntityLevelChangeEvent $e
	 */
	public function onLevelChange(EntityLevelChangeEvent $e) {
		$p = $e->getEntity();
		if ($p instanceof Player) {
			if ($this->hungerGamesAPI->getStorage()->isPlayerSet($p)) {
				$game = $this->hungerGamesAPI->getStorage()->getPlayerGame($p);
				if ($game !== null) {
					$this->hungerGamesAPI->getGlobalManager()->getGameManager($game)->removePlayer($p, true);
				}
			} elseif ($this->hungerGamesAPI->getStorage()->isPlayerWaiting($p)) {
				$game = $this->hungerGamesAPI->getStorage()->getWaitingPlayerGame($p);
				if ($game !== null) {
					$this->hungerGamesAPI->getGlobalManager()->getGameManager($game)->removeWaitingPlayer($p, true);
				}
			}
		}
	}

	/**
	 * @param PlayerQuitEvent $e
	 */
	public function playerQuitEvent(PlayerQuitEvent $e) {
		$p = $e->getPlayer();
		if ($this->hungerGamesAPI->getStorage()->isPlayerSet($p)) {
			$game = $this->hungerGamesAPI->getStorage()->getPlayerGame($p);
			if ($game !== null) {
				$this->hungerGamesAPI->getGlobalManager()->getGameManager($game)->removePlayer($p, true);
			}
		} elseif ($this->hungerGamesAPI->getStorage()->isPlayerWaiting($p)) {
			$game = $this->hungerGamesAPI->getStorage()->getWaitingPlayerGame($p);
			if ($game !== null) {
				$this->hungerGamesAPI->getGlobalManager()->getGameManager($game)->removeWaitingPlayer($p, true);
			}
		}
	}

	/**
	 * @param PlayerDeathEvent $e
	 */
	public function playerDeathEvent(PlayerDeathEvent $e) {
		$p = $e->getEntity();
		if ($this->hungerGamesAPI->getStorage()->isPlayerSet($p)) {
			$game = $this->hungerGamesAPI->getStorage()->getPlayerGame($p);
			if ($game !== null) {
				$this->hungerGamesAPI->getGlobalManager()->getGameManager($game)->removePlayer($p);
			}
			$count = $this->hungerGamesAPI->getStorage()->getPlayersInGameCount($game);
			if ($count > 1) {
				$msg = Msg::getHGMessage("hg.message.death");
				$msg = str_replace(["%player%", "%game%", "%left%"], [$p->getName(), $game->getName(), $count], $msg);
				$this->hungerGamesAPI->getGlobalManager()->getGameManager($game)->sendGameMessage(Msg::color($msg));
			}
		}
	}

	/**
	 * @param BlockBreakEvent $e
	 */
	public function onBlockBreak(BlockBreakEvent $e) {
		if ($this->hungerGamesAPI->getStorage()->isPlayerWaiting($e->getPlayer())) {
			$e->setCancelled();
		}
	}

}
