# HungerGames

A HungerGames plugin for PocketMine-MP.

## Installation

Grab the latest build from **[Poggit CI](https://poggit.pmmp.io/ci/IceCruelStuff/HungerGames-1)** and put it into your `plugins` folder.

### How to setup a join sign?

On the sign, set the first line to "hg". The second line should be the name of your game.

The sign should now refresh automatically and you'll be able to join.

You can also join a game by typing `/hg join <game>`.

### For Developers

This plugin comes with a script loader API. You can use this to access game functions, like when player joins, quits, wins, etc. You do not need to enable it, as it loads itself.

<details>
<summary><strong>Example Code 📖</strong></summary>

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

### Game Commands

#### /hg join
**Usage:** `/hg join <game>`

**Description:** Joins a new game

**Permission:** None

#### /hg leave
**Usage:** `/hg leave`

**Description:** Leaves the game that you are playing

**Permission** None

### Setup Commands

#### /hg add
**Usage:** `/hg add <game>`

**Description:** Adds a new game

**Permission:** `hg.command.add` (OP)

#### /hg del
**Usage:** `/hg del <game>`

**Description:** Deletes a game

**Permission:** `hg.command.del` (OP)

#### /hg min
**Usage:** `/hg min <game> <number>`

**Description:** Changes the number of minimum players required to start a game

**Permission:** `hg.command.min` (OP)

#### /hg max
**Usage:** `/hg max <game> <number>`

**Description:** Changes the number of maximum players that can enter a game

**Permission:** `hg.command.max` (OP)

#### /hg level
**Usage:** `/hg level <game> <level name>`

**Description:** Changes what level the players are going to go

**Permission:** `hg.command.level` (OP)

#### /hg ws
**Usage:** `/hg ws <game> <number>`

**Description:** Sets amount of seconds to wait before the game starts

**Permission:** `hg.command.ws` (OP)

#### /hg gs
**Usage:** `/hg gs <game> <number>`

**Description:** Sets the amount of seconds to pass before the death match starts

**Permission:** `hg.command.gs` (OP)

#### /hg addslot
**Usage:** `/hg addslot <game> <name>`

**Description:** Adds a new slot to the game (positions sets where you are standing)

**Permission:** `hg.command.slot.add` (OP)

#### /hg delslot
**Usage:** `/hg delslot <game> <name>`

**Description:** Deletes a slot from game by name

**Permission:** `hg.command.slot.del` (OP)

#### /hg lobby
**Usage:** `/hg lobby <game>`

**Description:** Sets the lobby position of a game where you're standing

**Permission:** `hg.command.lobby` (OP)

#### /hg dm
**Usage:** `/hg dm <game>`

**Description:** Sets the death match position of a game where you're standing

**Permission:** `hg.command.dm` (OP)
