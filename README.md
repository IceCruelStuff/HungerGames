# HungerGames
=============

## Installation

Grab the latest build from **[Poggit CI](https://poggit.pmmp.io/ci/IceCruelStuff/HungerGames-1)** and put it into your `plugins` folder.

### How to setup a join sign?

On the sign, set the first line to "hg". The second line should be the name of your game.

The sign should now refresh automatically and you'll be able to join.

You can also join a game by typing `/hg join <game>`.

### For Developers

This plugin comes with a script loader API. You can use this to access game functions, like when player joins, quits, wins, etc. You do not need to enable it, as it loads itself.

<details>
<summary>Example Code</summary>

```php
<?php

use hungergames\api\scripts\HGAPIScript;

class ExampleScript extends HGAPIScript {

    public function __construct() {
        parent::__construct("Script name", "Versions here", "Author");
    }

    public function onLoad() {
        $this->sendConsoleMessage("Test script loaded!");
    }
}

// functions from this script

/**
 * Creates script config
 *
 * @param $name
 * @param array $values
 * @return Config
 */
public function createConfig($name, array $values);

/**
 * Gets script config
 *
 * @return Config
 */
public function getConfig();

/**
 * Gets the name of the script
 *
 * @return string
 */
public function getName();

/**
 * Gets the name of the script
 *
 * @return string
 */
public function getVersion();

/**
 * Gets the author of the script
 *
 * @return string
 */
public function getAuthor();

/**
 * Disables script
 */
public function setDisabled();

/**
 * Enables script
 */
public function setEnabled();

/**
 * Returns whether script is enabled or not
 *
 * @return bool
 */
public function isEnabled();

/**
 * Sends console message
 *
 * @param $message
 */
public function sendConsoleMessage($message);

/**
 * Called when script is loaded
 */
public function onLoad() : void {
    // code
}

/**
 * Called when player joins game
 *
 * @param Player $player
 * @param HungerGames $game
 */
public function onPlayerJoinGame(Player $player, HungerGames $game) {
    // code
}

/**
 * Called when player quits game
 *
 * @param Player $player
 * @param HungerGames $game
 */
public function onPlayerQuitGame(Player $player, HungerGames $game) {
    // code
}

/**
 * Called when players wins a game
 *
 * @param Player $player
 * @param HungerGames $game
 */
public function onPlayerWinGame(Player $player, HungerGames $game) {
    // code
}

/**
 * Called when players lose a game
 *
 * @param Player $player
 * @param HungerGames $game
 */
public function onPlayerLoseGame(Player $player, HungerGames $game) {
    // code
}

/**
 * Called when player fails to join full game
 *
 * @param Player $player
 * @param HungerGames $game
 */
public function gameIsFull(Player $player, HungerGames $game) {
    // code
}

/**
 * Called when player is waiting for players
 *
 * @param array $players
 * @param HungerGames $game
 */
public function whileWaitingForPlayers(array $players, HungerGames $game) {
    // code
}

/**
 * Called when player is waiting for players
 *
 * @param array $players
 * @param HungerGames $game
 */
public function whileWaitingToStart(array $players, HungerGames $game) {
    // code
}

/**
 * Called when game starts
 *
 * @param array $players
 * @param HungerGames $game
 */
public function onGameStart(array $players, HungerGames $game) {
    // code
}

/**
 * Called when death match starts
 *
 * @param array $players
 * @param HungerGames $game
 */
public function onDeathMatchStart(array $players, HungerGames $game) {
    // code
}
```
</details>

### Commands:

* /hg add <game> : adds a new game
  * OP perm: hg.command.add
    
* /hg del <game> : deletes a game
  * OP perm: hg.command.del
  
* /hg min <game> <number> : changes the number of minimum players required to start a game
  * OP perm: hg.command.min
  
* /hg max <game> <number> : changes number of maximum players that can enter a game
  * OP perm: hg.command.max

* /hg level <game> <level name> : changes level of game where players are gonna go
  * OP perm: hg.command.level

* /hg ws <game> <number> : sets amount of seconds to wait before game starts  
  * OP perm: hg.command.ws

* /hg gs <game> <number> : sets amount of second to pass before death match starts
  * OP perm: hg.command.gs

* /hg addslot <game> <name> : adds new slot to game (positions sets where you are standing)
  * OP perm: hg.command.slot.add

* /hg delslot <game> <name> : deletes slot from game by name
  * OP perm: hg.command.slot.del

* /hg lobby <game> : sets the lobby position of a game where you're standing
  * OP perm: hg.command.lobby
  
* /hg dm <game> : sets the death match position of a game where you're standing
  * OP perm: hg.command.dm

* /hg leave : leaves game that you are playing
  * OP perm: none
  
* /hg join <game> : join a new game
  * OP perm: none
