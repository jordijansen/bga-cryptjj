<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * CryptJj implementation : © Jordi Jansen <thestartplayer@gmail.com>
 * 
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * material.inc.php
 *
 * CryptJj game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *   
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */


/*

Example:

$this->card_types = array(
    1 => array( "card_name" => ...,
                ...
              )
);

*/

$this->die_types = [
    'servant_die' => [
        'name' => totranslate("Servant Die")
    ]
];

$this->treasure_types = [
    'tapestry' => [
        'name' => totranslate("Tapestry")
    ],
    'idol' => [
        'name' => totranslate("Idol")
    ],
    'remains' => [
        'name' => totranslate("Remains")
    ],
    'pottery' => [
        'name' => totranslate("Pottery")
    ],
    'jewelery' => [
        'name' => totranslate("Jewelery")
    ],
    'manuscript' => [
        'name' => totranslate("Manuscript")
    ]
];

$this->treasure_values = [
    1, 2, 3, 4
];

$this->card_types = [
    'collector' => [
        'name' => totranslate("Collector")
    ],
    'treasure' => [
        'name' => totranslate("Treasure")
    ]
];


