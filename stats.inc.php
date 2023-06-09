<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Crypt implementation : © Jordi Jansen <thestartplayer@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * stats.inc.php
 *
 * Crypt game statistics description
 *
 */

/*
    In this file, you are describing game statistics, that will be displayed at the end of the
    game.
    
    !! After modifying this file, you must use "Reload  statistics configuration" in BGA Studio backoffice
    ("Control Panel" / "Manage Game" / "Your Game")
    
    There are 2 types of statistics:
    _ table statistics, that are not associated to a specific player (ie: 1 value for each game).
    _ player statistics, that are associated to each players (ie: 1 value for each player in the game).

    Statistics types can be "int" for integer, "float" for floating point values, and "bool" for boolean
    
    Once you defined your statistics there, you can start using "initStat", "setStat" and "incStat" method
    in your game logic, using statistics names defined below.
    
    !! It is not a good idea to modify this file when a game is running !!

    If your game is already public on BGA, please read the following before any change:
    http://en.doc.boardgamearena.com/Post-release_phase#Changes_that_breaks_the_games_in_progress
    
    Notes:
    * Statistic index is the reference used in setStat/incStat/initStat PHP method
    * Statistic index must contains alphanumerical characters and no space. Example: 'turn_played'
    * Statistics IDs must be >=10
    * Two table statistics can't share the same ID, two player statistics can't share the same ID
    * A table statistic can have the same ID than a player statistics
    * Statistics ID is the reference used by BGA website. If you change the ID, you lost all historical statistic data. Do NOT re-use an ID of a deleted statistic
    * Statistic name is the English description of the statistic as shown to players
    
*/

require_once("modules/CryptGlobals.inc.php");

$stats_type = [

    // Statistics global to table
    "table" => [

        STAT_TABLE_NUMBER_OF_ROUNDS_PLAYED => array("id"=> 10,
                    "name" => totranslate("Number of rounds"),
                    "type" => "int" ),

    ],
    
    // Statistics existing for each player
    "player" => [

        STAT_PLAYER_CLAIM_TREASURE_ACTION_COUNT => array("id"=> 11,
                    "name" => totranslate("Number of Claim Treasure actions"),
                    "type" => "int" ),

        STAT_PLAYER_RECOVER_SERVANTS_ACTION_COUNT => array("id"=> 12,
            "name" => totranslate("Number of Recover Servant actions"),
            "type" => "int" ),

        STAT_PLAYER_TREASURE_CARD_COUNT => array("id"=> 13,
            "name" => totranslate("Number of Treasure Cards collected"),
            "type" => "int" ),

        STAT_PLAYER_COLLECTOR_ACTIVATED_COUNT => array("id"=> 14,
            "name" => totranslate("Number of Collectors activated"),
            "type" => "int" ),
    ]

];
