<?php
namespace xbeastmode\hg\event;
use pocketmine\event\plugin\PluginEvent;
use xbeastmode\hg\Loader;
abstract class HGEvent extends PluginEvent{
    /**
     * @param Loader $main
     */
    public function __construct(Loader $main){
        parent::__construct($main);
    }
}
