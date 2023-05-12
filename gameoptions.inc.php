<?php

require_once("modules/CryptGlobals.inc.php");

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Crypt implementation : © Jordi Jansen <thestartplayer@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * gameoptions.inc.php
 *
 * Crypt game options description
 * 
 * In this file, you can define your game options (= game variants).
 *   
 * Note: If your game has no variant, you don't have to modify this file.
 *
 * Note²: All options defined in this file should have a corresponding "game state labels"
 *        with the same ID (see "initGameStateLabels" in crypt.game.php)
 *
 * !! It is not a good idea to modify this file when a game is running !!
 *
 */

$game_options = array(

    /*
    
    // note: game variant ID should start at 100 (ie: 100, 101, 102, ...). The maximum is 199.
    100 => array(
                'name' => totranslate('my game option'),    
                'values' => array(

                            // A simple value for this option:
                            1 => array( 'name' => totranslate('option 1') )

                            // A simple value for this option.
                            // If this value is chosen, the value of "tmdisplay" is displayed in the game lobby
                            2 => array( 'name' => totranslate('option 2'), 'tmdisplay' => totranslate('option 2') ),

                            // Another value, with other options:
                            //  description => this text will be displayed underneath the option when this value is selected to explain what it does
                            //  beta=true => this option is in beta version right now (there will be a warning)
                            //  alpha=true => this option is in alpha version right now (there will be a warning, and starting the game will be allowed only in training mode except for the developer)
                            //  nobeginner=true  =>  this option is not recommended for beginners
                            //  firstgameonly=true  =>  this option is recommended only for the first game (discovery option)
                            3 => array( 'name' => totranslate('option 3'), 'description' => totranslate('this option does X'), 'beta' => true, 'nobeginner' => true )
                        ),
                'default' => 1
            ),

    */

    OPTION_COLLECTORS => array(
        'name' => totranslate('Collector Cards'),
        'values' => array(
            OPTION_COLLECTORS_A => array(
                'name' => totranslate('A side'),
                'description' => totranslate('Use the A side of the Collector Cards'),
                'tmdisplay' => totranslate('[A Collectors]'),
                'firstgameonly' => false,
                'nobeginner' => false
            ),
            OPTION_COLLECTORS_RANDOM => array(
                'name' => totranslate('A or B side (random)'),
                'description' => totranslate('Use the A or B side of the Collector Cards, chosen randomly'),
                'tmdisplay' => totranslate('[A or B Collectors]'),
                'firstgameonly' => false,
                'nobeginner' => true
            ),
            OPTION_COLLECTORS_B => array(
                'name' => totranslate('B side'),
                'description' => totranslate('Use the B side of the Collector Cards'),
                'tmdisplay' => totranslate('[B Collectors]'),
                'firstgameonly' => false,
                'nobeginner' => true
            ),
        ),
        'default' => 1
    )
);


