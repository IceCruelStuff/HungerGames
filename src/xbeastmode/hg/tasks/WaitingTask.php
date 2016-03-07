<?php
namespace xbeastmode\hg\tasks;
use pocketmine\item\Item;
use pocketmine\level\sound\AnvilUseSound;
use pocketmine\level\sound\AnvilFallSound;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use pocketmine\tile\Chest;

use xbeastmode\hg\HGManagement;
use xbeastmode\hg\Loader;
use xbeastmode\hg\utility\HGGame;
use xbeastmode\hg\utils\FMT;
class WaitingTask extends PluginTask{
    private $game;
    /** @var Loader */
    private $main;
    /** @var int */
    private $secs;
    public function __construct(Loader $e, $game, $secs){
        parent::__construct($e);
        $this->game = $game;
        $this->main = $e;
        $this->secs = $secs;
    }
    public function onRun($currentTick)
    {
        $this->secs--;
        if($this->secs === 1){
            foreach($this->main->getServer()->getLevelByName($this->main->getConfig()->getAll()["hg_games"][$this->game]["level"])->getTiles() as $chest){
                if($chest instanceof Chest) {
                    $chest->getInventory()->clearAll();
                    for ($i = 0; $i < 8; ++$i) {
                        $items = array(
                            Item::get(0, 0, 0),
                            Item::get(17, 0, mt_rand(5,10)),
                            Item::get(Item::BREAD, 0, 6),
                            Item::get(0, 0, 0),
                            Item::get(260, 0, 4),
                            Item::get(268, 0, 1),
                            Item::get(0, 0, 0),
                            Item::get(306, 0, 1),
                            Item::get(308, 0, 1),
                            Item::get(0, 0, 0),
                            Item::get(301, 0, 1),
                            Item::get(299, 0, 1),
                            Item::get(0, 0, 0),
                            Item::get(267, 0, 1),
                            Item::get(264, 0, mt_rand(0, 2)),
                            Item::get(0, 0, 0),
                            Item::get(265, 0, mt_rand(0, 3)),
                            Item::get(266, 0, mt_rand(0, 3))
                        );
                        $itemRnd = mt_rand(0, count($items) - 1);
                        $item = $items[$itemRnd];
                        $chest->getInventory()->addItem($item);
                    }
                }
            }
        }
        foreach(HGGame::getApi()->players[$this->game] as $p){
            if($p instanceof Player) {
                $p->sendTip(FMT::colorMessage(str_replace("%seconds%", $this->secs, $this->main->getMessage("waiting_tip"))));
                if ($this->secs <= 0) {
                    $this->main->getServer()->getScheduler()->cancelTask($this->getTaskId());
                    $time = HGGame::getApi()->getGameTime($this->game) / 60;
                    $cf = $this->main->getMessage("match_started");
                    $msg = FMT::colorMessage(str_replace(["%minutes%", "%game%"], [$time, $this->game], $cf));
                    $p->sendMessage($msg);
                    $this->main->createGameTask($this->game);
                    unset(HGManagement::$players[$this->game]);
                    HGManagement::$games[$this->game] = $this->game;
                    $p->getLevel()->addSound(new AnvilUseSound(new Vector3($p->x, $p->y, $p->z)));
                }
                if ($this->secs >= 1 and $this->secs <= 10) {
                    $p->getLevel()->addSound(new AnvilFallSound(new Vector3($p->x, $p->y, $p->z)));
                }
            }
        }
    }
}
