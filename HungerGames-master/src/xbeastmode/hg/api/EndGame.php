<?php
namespace xbeastmode\hg\api;
use pocketmine\block\Block;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\entity\Item;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;
use xbeastmode\hg\event\player\PlayerWinGameEvent;
use xbeastmode\hg\HGManagement;
use xbeastmode\hg\Loader;
use xbeastmode\hg\utils\FMT;
class EndGame{
    /** @var Loader */
    private $main;
    /**
     * @param Loader $p
     */
    public function __construct(Loader $p){
        $this->main = $p;
    }
    public function tpAll(){
        foreach(HGGame::getApi()->players as $p){
            if($p instanceof Player){
                $game = HGManagement::$data[$p->getName()];
                $p->teleport(HGGame::getApi()->getLobbyPosition($game));
            }
        }
    }
    /**
     * @param Player $player
     */
    public function deletePlayerData(Player $player){
        $game = HGManagement::$data[$player->getName()];
        unset(HGManagement::$data[$player->getName()]);
        unset(HGManagement::$players[$game][$player->getName()]);
        unset(HGGame::getApi()->players[$game][spl_object_hash($player)]);
    }
    /**
     * @param $game
     */
    public function deleteGameData($game){
        unset(HGManagement::$games[$game]);
        unset(HGManagement::$players[$game]);
        unset(HGGame::getApi()->counters[$game]);
        unset(HGGame::getApi()->players[$game]);
        unset(HGGame::getApi()->onWait[$game]);
        unset($this->main->tasks[$game]);
    }
    /**
     * @param $game
     */
    public function endGame($game){
        if(isset(HGGame::getApi()->onWait[$game])) {
            $pos = $this->main->getConfig()->getAll()["hg_games"][$game]["lobby_pos"];
            $level = $this->main->getServer()->getLevelByName($this->main->getConfig()->getAll()["hg_games"][$game]["lobby_pos"]["level"]);
            foreach (HGGame::getApi()->players[$game] as $p) {
                if ($p instanceof Player) {
                    $p->teleport(new Position($pos["x"], $pos[1], $pos["z"], $level));
                    $pwge = new PlayerWinGameEvent($this->main, $p, $game);
                    $this->main->getServer()->getPluginManager()->callEvent($pwge);
                    if($pwge->isCancelled()) return;
                    $msg = FMT::colorMessage($this->main->getMessage("won_match"));
                    $msg = str_replace(["%player%", "%game%"], [$p->getName(), $game], $msg);
                    $this->main->getServer()->broadcastMessage($msg);
                    $ent = $p->getLevel()->getEntities();
                    foreach ($this->main->getConfig()->get("winning_commands") as $cm) {
                        $this->main->getServer()->dispatchCommand(new ConsoleCommandSender(), str_replace(["%player%", "%game%"], [$p->getName(), $game], $cm));
                    }
                    foreach ($ent as $e) {
                        if ($e instanceof Item) {
                            $p->getLevel()->removeEntity($e);
                        }
                    }
                }
                break;
            }
        }
    }
    /**
     * @param $game
     */
    public function resetMap($game){
        if(isset(HGManagement::$BBlocks[$game])) {
            foreach (HGManagement::$BBlocks[$game] as $b) {
                if ($b instanceof Block) {
                    $lvl = $b->getLevel();
                    $lvl->setBlock(new Vector3($b->x, $b->y, $b->z), Block::get($b->getId(), 0));
                }
            }
        }
        if(isset(HGManagement::$PBlocks[$game])) {
            foreach (HGManagement::$PBlocks[$game] as $p) {
                if ($p instanceof Block) {
                    $lvl = $p->getLevel();
                    $lvl->setBlock(new Vector3($p->x, $p->y, $p->z), Block::get(0, 0));
                }
            }
        }
        unset(HGManagement::$BBlocks[$game]);
        unset(HGManagement::$PBlocks[$game]);
    }
}
