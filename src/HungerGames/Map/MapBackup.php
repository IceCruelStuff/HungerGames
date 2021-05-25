<?php

namespace HungerGames\Map;

use HungerGames\Loader;
use HungerGames\Tasks\AsyncMapBackup;

class MapBackup {

    /** @var Loader */
    public $loader;

    /**
     *
     * MapBackup constructor.
     *
     * @param Loader $loader
     *
     */
    public function __construct(Loader $loader) {
        $this->loader = $loader;
    }

    /**
     *
     * Writes folder backup
     *
     * @param string $source
     * @param string $destination
     * @param string $game
     *
     * @return void
     */
    public function write(string $source, string $destination, string $game) {
        $this->asyncWrite($source, $destination, $game);
    }

    /**
     *
     * @param string $source
     * @param string $destination
     * @param string $game
     */
    public function asyncWrite(string $source, string $destination, string $game) {
        $this->loader->getServer()->getAsyncPool()->submitTask(new AsyncMapBackup($source, $destination, $game));
    }

    /**
     *
     * Resets game map
     *
     * @param string $source
     * @param string $destination
     * @param string $game
     *
     * @return void
     *
     */
    public function reset(string $source, string $destination, string $game) {
        $this->asyncWrite($source, $destination, $game);
    }

}
