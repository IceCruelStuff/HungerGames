<?php
namespace xbeastmode\hg;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat as color;

use xbeastmode\hg\api\HGGame;
use xbeastmode\hg\utils\FMT;
class Events implements Listener{
    /** @var Loader */
    private $main;
    /**
     * @param Loader $main
     */
    public function __construct(Loader $main){
        $this->main = $main;
    }
    /**
     * @param SignChangeEvent $e
     */
    public function onSignChange(SignChangeEvent $e){
        $line = $e->getLines();
        $p = $e->getPlayer();
        if (strtolower($line[0]) === strtolower("HG") and $p->isOp()){
            if(!$e->getPlayer()->isOp()) return;
            if(!isset($this->main->getConfig()->getAll()["hg_games"][color::clean($line[1])])){
                $p->sendMessage(FMT::colorMessage("&cError[CS-1]: &egame does not exist."));
                return;
            }
            $e->setLine(0, FMT::colorMessage($this->main->getConfig()->getAll()["sign"]["line1"]));
            $e->setLine(1, $line[1]);
            $p->sendMessage(color::GREEN . "Successfully created sign for game '" . $line[1] . "'!");
        }
    }
    /**
     * @param PlayerInteractEvent $e
     */
    public function onInteract(PlayerInteractEvent $e)
    {
        $sign = $e->getBlock()->getLevel()->getTile($e->getBlock());
        if ($sign instanceof Sign) {
            $clean = color::clean($sign->getText()[1], true);
            if (isset($this->main->getConfig()->getAll()["hg_games"][$clean]) and !isset(HGManagement::$games[$clean])) {
                $e->getPlayer()->getInventory()->clearAll();
                foreach(HGGame::getApi()->players as $g){
                    if(isset($g[spl_object_hash($e->getPlayer())])){
                        $e->getPlayer()->sendMessage(FMT::colorMessage("&cError[J-1]: &ealready joined game."));
                        return;
                    }
                    break;
                }
                if(isset(HGGame::getApi()->players[$clean][spl_object_hash($e->getPlayer())])){
                    $clean->sendMessage(FMT::colorMessage("&cError[J-1]: &ealready joined game."));
                    return;
                }
                if(HGGame::getApi()->tpToOpenSlot($e->getPlayer(), $clean) === false){
                    return;
                }
                HGManagement::$players[$clean][$e->getPlayer()->getName()] = $e->getPlayer();
                HGManagement::$data[$e->getPlayer()->getName()] = $clean;
                foreach(HGGame::getApi()->players[$clean] as $p){
                    if($p instanceof Player) {
                        $p->sendMessage(FMT::colorMessage(str_replace(["%player%", "%game%"], [$e->getPlayer()->getName(), $clean], $this->main->getMessage("joined_game"))));
                    }
                    break;
                }
            }elseif(isset(HGManagement::$games[$clean])){
                $e->getPlayer()->sendMessage(FMT::colorMessage(str_replace("%game%", $clean, $this->main->getMessage("already_running"))));
                return;
            }
        }
    }
    /**
     * @param PlayerMoveEvent $e
     */
    public function onMove(PlayerMoveEvent $e){
        if(!isset(HGManagement::$data[$e->getPlayer()->getName()])) return;
        $clean = HGManagement::$data[$e->getPlayer()->getName()];
        if(isset(HGManagement::$players[$clean][$e->getPlayer()->getName()])){
            $from = clone $e->getFrom();
            $to = $e->getTo();
            $from->yaw = $to->yaw;
            $from->pitch = $to->pitch;
            $e->setTo($from);
        }
    }
    /**
     * @param PlayerDeathEvent $e
     */
    public function onKill(PlayerDeathEvent $e){
        $player = $e->getEntity();
        if($player instanceof Player) {
            if(!isset(HGManagement::$data[$player->getName()])) return;
            if(isset(HGGame::getApi()->players[HGManagement::$data[$player->getName()]][spl_object_hash($player)])) {
                HGGame::getApi()->onWait[HGManagement::$data[$player->getName()]] -= 1;
                $onWait = HGGame::getApi()->onWait[HGManagement::$data[$player->getName()]];
                if($onWait == 0){
                    $this->main->e->deleteGameData(HGManagement::$data[$player->getName()]);
                }
                if ($onWait == 1) {
                    $this->main->e->endGame(HGManagement::$data[$player->getName()]);
                    $this->main->e->deleteGameData(HGManagement::$data[$player->getName()]);
                }
                $this->main->e->deletePlayerData($player);
            }
        }
    }
    /**
     * @param PlayerQuitEvent $e
     */
    public function onQuit(PlayerQuitEvent $e){
        $player = $e->getPlayer();
        if(!isset(HGManagement::$data[$player->getName()])) return;
        if(isset(HGGame::getApi()->players[HGManagement::$data[$player->getName()]][spl_object_hash($player)])) {
            HGGame::getApi()->onWait[HGManagement::$data[$player->getName()]] -= 1;
            $onWait = HGGame::getApi()->onWait[HGManagement::$data[$player->getName()]];
            if($onWait == 0){
                $this->main->e->deleteGameData(HGManagement::$data[$player->getName()]);
            }
            if ($onWait == 1) {
                $this->main->e->endGame(HGManagement::$data[$player->getName()]);
                $this->main->e->deleteGameData(HGManagement::$data[$player->getName()]);
            }
            $this->main->e->deletePlayerData($player);
        }
    }
}
