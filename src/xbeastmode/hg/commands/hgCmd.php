<?php
namespace xbeastmode\hg\commands;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use xbeastmode\hg\HGManagement;
use xbeastmode\hg\Loader;
use xbeastmode\hg\utility\HGGame;
use xbeastmode\hg\utils\FMT;
class hgCmd extends Command implements PluginIdentifiableCommand{
    /** @var Loader */
    private $main;
    /**
     * @param Loader $main
     */
    public function __construct(Loader $main){
        parent::__construct("hg", "Hunger Games command", FMT::colorMessage("&e/hg help"), ["sg", "hgg", "sgg", "sggame", "hggame"]);
        $this->main = $main;
    }
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param string[] $args
     *
     * @return mixed
     */
    public function execute(CommandSender $sender, $commandLabel, array $args)
    {
        if($sender instanceof Player) {
            if (empty($args[0])) {
                $sender->sendMessage(FMT::colorMessage($this->getUsage()));
                return;
            }
            switch (strtolower($args[0])) {
                case 'help':
                    if ($sender->isOp()) {
                        $sender->sendMessage(FMT::colorMessage("&f-=[&eHunger&6Games&f]=-"));
                        $sender->sendMessage(FMT::colorMessage("&e/hg join <game> &fjoin game"));
                        $sender->sendMessage(FMT::colorMessage("&e/hg quit &fquit game"));
                        $sender->sendMessage(FMT::colorMessage("&e/hg addslot <game> &f add slot to game"));
                        $sender->sendMessage(FMT::colorMessage("&e/hg create <name> &fcreate a game"));
                        $sender->sendMessage(FMT::colorMessage("&e/hg min <game> <number> &fset minimum players for game"));
                        $sender->sendMessage(FMT::colorMessage("&e/hg max <game> <number> &fset maximum players for game"));
                        $sender->sendMessage(FMT::colorMessage("&e/hg time <game> <game|wait> <number> &fchange game/wait time"));
                        $sender->sendMessage(FMT::colorMessage("&e/hg level <game> [level] &fchange level for a game"));
                    } else {
                        $sender->sendMessage(FMT::colorMessage("&e/hg join <game> &fjoin game"));
                        $sender->sendMessage(FMT::colorMessage("&e/hg quit &fquit game"));
                    }
                    break;
                case 'j':
                case 'join':
                    if (empty($args[1])) {
                        $sender->sendMessage(FMT::colorMessage("&e/hg join <game> &fjoin game"));
                        return;
                    }
                    $game = $args[1];
                    foreach(HGGame::getApi()->players as $g){
                        if(isset($g[spl_object_hash($sender)])){
                            $sender->sendMessage(FMT::colorMessage("&cError[J-1]: &ealready joined game."));
                            return;
                        }
                        break;
                    }
                    if(isset(HGGame::getApi()->players[$game][spl_object_hash($sender)])){
                        $sender->sendMessage(FMT::colorMessage("&cError[J-1]: &ealready joined game."));
                        return;
                    }
                    if (!isset($this->main->getConfig()->getAll()["hg_games"][$game])) {
                        $sender->sendMessage(FMT::colorMessage("&cError[J-2]: &egame does not exist."));
                        return;
                    }
                    if(HGGame::getApi()->tpToOpenSlot($sender, $game) === false){
                        return;
                    }
                    HGManagement::$data[$sender->getName()] = $game;
                    HGManagement::$players[$game][$sender->getName()] = $sender;
                    foreach (HGGame::getApi()->players[$game] as $p) {
                        if ($p instanceof Player) {
                            $p->sendMessage(FMT::colorMessage(str_replace(["%player%", "%game%"], [$sender->getName(), $game], $this->main->getMessage("joined_game"))));
                        }
                        break;
                    }
                break;
                case 'q':
                case 'quit':
                    if(isset(HGManagement::$data[$sender->getName()])){
                        $game = HGManagement::$data[$sender->getName()];
                        if(isset(HGGame::getApi()->players[$game][spl_object_hash($sender)])){
                            $sender->teleport(HGGame::getApi()->getLobbyPosition($game));
                            HGGame::getApi()->onWait[HGManagement::$data[$sender->getName()]] -= 1;
                            $onWait = HGGame::getApi()->onWait[HGManagement::$data[$sender->getName()]];
                            $sender->sendMessage(FMT::colorMessage("&aQuiting..."));
                            if($onWait == 0){
                                $this->main->e->deleteGameData(HGManagement::$data[$sender->getName()]);
                            }
                            if($onWait == 1){
                                $this->main->e->endGame(HGManagement::$data[$sender->getName()]);
                                $this->main->e->deleteGameData(HGManagement::$data[$sender->getName()]);
                            }
                            $this->main->e->deletePlayerData($sender);
                        }else{
                            $sender->sendMessage(FMT::colorMessage("&cYou are not in a game."));
                        }
                    }
                    break;
                case 'as':
                case 'addslot':
                if ($sender->isOp()) {
                    if (empty($args[1])) {
                        $sender->sendMessage(FMT::colorMessage("&e/hg addslot <game> &f add slot to game"));
                        return;
                    }
                    $game = $args[1];
                    if (!isset($this->main->getConfig()->getAll()["hg_games"][$game])) {
                        $sender->sendMessage(FMT::colorMessage("&cError[S-1]: &egame does not exist."));
                        return;
                    }
                    $slot = count($this->main->getConfig()->getAll()["hg_games"][$game]["slots"]) + 1;
                    $x = $sender->x;
                    $y = $sender->y;
                    $z = $sender->z;
                    $this->main->getConfig()->setNested("hg_games.$game.slots.slot$slot", ["x" => $x, 1 => $y, "z" => $z]);
                    $this->main->getConfig()->setAll($this->main->getConfig()->getAll());
                    $this->main->getConfig()->save();
                    $sender->sendMessage(FMT::colorMessage("&aSuccessfully added slot at your current position."));
                }
                    break;
                case 'c':
                case 'create':
                if ($sender->isOp()) {
                    if (empty($args[1])) {
                        $sender->sendMessage(FMT::colorMessage("&e/hg create <name> &fcreate a game"));
                        return;
                    }
                    $game = $args[1];
                    if (isset($this->main->getConfig()->getAll()["hg_games"][$game])) {
                        $sender->sendMessage(FMT::colorMessage("&cError[C-1]: &egame already exists."));
                        return;
                    }
                    $s = $this->main->getServer()->getDefaultLevel()->getSpawnLocation();
                    $contents =
                        ["min_players" => 2,
                            "max_players" => 6,
                            "game_time" => 300,
                            "wait_time" => 60,
                            "level" => $sender->getLevel()->getName(),
                            "lobby_pos" =>
                                [
                                    "x" => $s->x,
                                    1 => $s->y,
                                    "z" => $s->z,
                                    "level" => $s->level->getName()
                                ],
                            "death_match_pos" =>
                                [
                                    "x" => $sender->x,
                                    1 => $sender->y,
                                    "z" => $sender->z,
                                    "level" => $sender->level->getName()
                                ],
                            "slots" => []
                        ];
                    $this->main->getConfig()->setNested("hg_games.$game", $contents);
                    $this->main->getConfig()->setAll($this->main->getConfig()->getAll());
                    $this->main->getConfig()->save();
                    $sender->sendMessage(FMT::colorMessage("&aSuccessfully created game $game."));
                }
                    break;
                case 'mn':
                case 'min':
                    if($sender->isOp()) {
                        if (empty($args[1]) || empty($args[2]) || !is_numeric($args[2])) {
                            $sender->sendMessage(FMT::colorMessage("&e/hg min <game> <number> &fset minimum players for game"));
                            return;
                        }
                        $game = $args[1];
                        if (!isset($this->main->getConfig()->getAll()["hg_games"][$game])) {
                            $sender->sendMessage(FMT::colorMessage("&cError[M-1]: &egame does not exist."));
                            return;
                        }
                        $min = intval($args[2]);
                        $this->main->getConfig()->setNested("hg_games.$game.min_players", $min);
                        $this->main->getConfig()->setAll($this->main->getConfig()->getAll());
                        $this->main->getConfig()->save();
                        $sender->sendMessage(FMT::colorMessage("&aSuccessfully changed minimum players to $min."));
                    }
                    break;
                case 'mx':
                case 'max':
                    if($sender->isOp()) {
                        if (empty($args[1]) || empty($args[2]) || !is_numeric($args[2])) {
                            $sender->sendMessage(FMT::colorMessage("&e/hg max <game> <number> &fset minimum players for game"));
                            return;
                        }
                        $game = $args[1];
                        if (!isset($this->main->getConfig()->getAll()["hg_games"][$game])) {
                            $sender->sendMessage(FMT::colorMessage("&cError[M-2]: &egame does not exist."));
                            return;
                        }
                        $max = intval($args[2]);
                        $this->main->getConfig()->setNested("hg_games.$game.max_players", $max);
                        $this->main->getConfig()->setAll($this->main->getConfig()->getAll());
                        $this->main->getConfig()->save();
                        $sender->sendMessage(FMT::colorMessage("&aSuccessfully changed maximum players to $max."));
                    }
                    break;
                case 't':
                case 'time':
                    if($sender->isOp()) {
                        if (empty($args[1]) || empty($args[2]) || !is_numeric($args[3])) {
                            $sender->sendMessage(FMT::colorMessage("&e/hg time <game> <game|wait> <number> &fchange game/wait time"));
                            return;
                        }
                        $time = intval($args[3]);
                        $game = $args[1];
                        if (!isset($this->main->getConfig()->getAll()["hg_games"][$game])) {
                            $sender->sendMessage(FMT::colorMessage("&cError[T-1]: &egame does not exist."));
                            return;
                        }
                        if(strtolower($args[2]) === "game"){
                            $this->main->getConfig()->setNested("hg_games.$game.game_time", $time);
                            $this->main->getConfig()->setAll($this->main->getConfig()->getAll());
                            $this->main->getConfig()->save();
                            $sender->sendMessage(FMT::colorMessage("&aSuccessfully changed game time for $game to $time."));
                        }
                        if(strtolower($args[2]) === "wait"){
                            $this->main->getConfig()->setNested("hg_games.$game.wait_time", $time);
                            $this->main->getConfig()->setAll($this->main->getConfig()->getAll());
                            $this->main->getConfig()->save();
                            $sender->sendMessage(FMT::colorMessage("&aSuccessfully changed wait time for $game to $time."));
                        }
                    }
                    break;
                case 'l':
                case 'lvl':
                case 'level':
                    if($sender->isOp()) {
                        if (empty($args[1]) || empty($args[2])) {
                            $sender->sendMessage(FMT::colorMessage("&e/hg level <game> <level> &fchange level for a game"));
                            return;
                        }
                        $game = $args[1];
                        if (!isset($this->main->getConfig()->getAll()["hg_games"][$game])) {
                            $sender->sendMessage(FMT::colorMessage("&cError[L-1]: &egame does not exist."));
                            return;
                        }
                        $level = $this->main->getServer()->getLevelByName($args[2]);
                        if(!$level){
                            $sender->sendMessage(FMT::colorMessage("&cError[L-2]: &einvalid level"));
                            return;
                        }
                        $this->main->getConfig()->setNested("hg_games.$game.level", $level->getName());
                        $this->main->getConfig()->setAll($this->main->getConfig()->getAll());
                        $this->main->getConfig()->save();
                        $sender->sendMessage(FMT::colorMessage("&aSuccessfully changed level for $game to ".$level->getName()."."));
                    }
                    break;
            }
        }else{
            $sender->sendMessage(FMT::colorMessage("&cPlease run command in-game."));
        }
    }
    /**
     * @return \pocketmine\plugin\Plugin
     */
    public function getPlugin()
    {
        return $this->main;
    }
}
