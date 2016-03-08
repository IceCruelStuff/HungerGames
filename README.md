# HungerGames
=============
A HungerGames plugin for PocketMine-MP developed by xBeastMode
==============================================================

Commands:
- hg help - show all commands
- hg join - join hg game
- hg quit - add hg game to config 
- hg addslot - add slot to a game
- hg min - set minimum players for game
- hg max - set maximum players for game
- hg time - change time for game

Command aliases: [sg, hgg, sgg, sggame, hggame]

Features:
+ auto chest filling (not customizable yet)
+ multi arena support
+ customizable status signs
+ customizable messages
+ customizable game settings

Setup:
You can set everything in the config. You can change the game however you want it and also configure it with commands.
If you want to use a sign:
* Line 1: hg
* Line 2: game_name

After you do that the plugin will automatically make a sign for it with all the current stats.
 You can also configure
sign looks in the config.

Gameplay:
1. Players enter match
2. They wait for as long as you set the time to
3. Match starts
4. Chests fill automatically
5. After X minutes/seconds death match starts

If a player wins the match you will be able to run commands with the winner's name.

Info:
Plugin created by xBeastMode
