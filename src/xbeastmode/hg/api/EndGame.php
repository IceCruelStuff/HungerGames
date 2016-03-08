<?php
namespace xbeastmode\hg\api;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\entity\Item;
use pocketmine\level\Position;
use pocketmine\Player;
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
                    $msg = FMT::colorMessage($this->main->getMessage("won_match"));
                    $msg = str_replace(["%player%", "%game%"], [$p->getName(), $game], $msg);
                    $this->main->getServer()->broadcastMessage($msg);
                    $ent = $p->getLevel()->getEntities();
                    foreach ($this->main->getConfig()->get("winning_commands") as $cm) {
                        $this->main->getServer()->dispatchCommand(new ConsoleCommandSender(), str_replace("%player%", $p->getName(), $cm));
                    }
                    foreach ($ent as $e) {
                        if ($e instanceof Item) {
                            $p->getLevel()->removeEntity($e);
                        }
                    }
                }
            }
        }
    }
}
