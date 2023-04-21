<?php

require_once("modules/CryptGlobals.inc.php");

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
$this->collectors = [
    'idol' => [
        'name' => totranslate("Idol Collector"),
        'sides' => [
            'A' => [
                'type' => COLLECTOR_COLLECT_PHASE,
                'description' => totranslate("During the Collect phase, flip an Idol face-up to re-roll one of your dice. An Idol may be flipped to re-roll on the same turn that it was collected."),
                'nrOfCardsToFlip' => 1
            ],
            'B' => [
                'type' => COLLECTOR_END_GAME,
                'description' => totranslate("The first player to collect two Idols flips them face-up and scores 5 bonus Coins at game end. If multiple players collect two Idols on the same turn, each flips their Idols and scores 5 bonus Coins. All subsequent players who collect two or more Idols keep them face-down and score 2 Coins."),
                'nrOfCardsToFlip' => 2
            ],
        ]
    ],
    'jewelery' => [
        'name' => totranslate("Jewelery Collector"),
        'sides' => [
            'A' => [
                'type' => COLLECTOR_END_GAME,
                'description' => totranslate("At game end, players with two or more Jewelry score their highest valued Jewelry twice."),
                'nrOfCardsToFlip' => 0
            ],
            'B' => [
                'type' => COLLECTOR_END_GAME,
                'description' => totranslate("At game end, all players score 1 bonus Coin for each Jewelry in their collection."),
                'nrOfCardsToFlip' => 0
            ],
        ]
    ],
    'manuscript' => [
        'name' => totranslate("Manuscript Collector"),
        'sides' => [
            'A' => [
                'type' => COLLECTOR_END_GAME,
                'description' => totranslate("At game end, players with two or more Manuscripts score each of their Manuscripts as 4 Coins instead of the value printed on the cards."),
                'nrOfCardsToFlip' => 0
            ],
            'B' => [
                'type' => COLLECTOR_ANY_TIME,
                'description' => totranslate("At any time, flip one Manuscript face-up to secretly view all the face- down card(s) in the Crypt."),
                'nrOfCardsToFlip' => 1
            ],
        ]
    ],
    'tapestry' => [
        'name' => totranslate("Tapestry Collector"),
        'sides' => [
            'A' => [
                'type' => COLLECTOR_END_GAME,
                'description' => totranslate("At game end, the player whose combined Tapestries are worth the most Coins scores 5 bonus Coins. Tied players each score the full bonus."),
                'nrOfCardsToFlip' => 0
            ],
            'B' => [
                'type' => COLLECTOR_END_GAME,
                'description' => totranslate("At game end, if only one player has three or more Tapestries, they score 7 bonus Coins. If more than one player collects three or more Tapestries, each player scores 4 bonus Coins."),
                'nrOfCardsToFlip' => 0
            ],
        ]
    ],
    'remains' => [
        'name' => totranslate("Remains Collector"),
        'sides' => [
            'A' => [
                'type' => COLLECTOR_ANY_TIME,
                'description' => totranslate("At any time, flip two Remains face-up to recover one exhausted Servant die."),
                'nrOfCardsToFlip' => 2
            ],
            'B' => [
                'type' => COLLECTOR_END_GAME,
                'description' => totranslate("At game end, players with four or more Remains score 10 bonus Coins."),
                'nrOfCardsToFlip' => 0
            ],
        ]
    ],
    'pottery' => [
        'name' => totranslate("Pottery Collector"),
        'sides' => [
            'A' => [
                'type' => COLLECTOR_END_GAME,
                'description' => totranslate("At game end, players with two Pottery score 2 bonus Coins, three Pottery score 4 bonus Coins, and four or more Pottery score 8 bonus Coins."),
                'nrOfCardsToFlip' => 0
            ],
            'B' => [
                'type' => COLLECTOR_BEFORE_CLAIM_PHASE,
                'description' => totranslate("Before the Claim phase, flip two Pottery face-up to take a face-down card from the Crypt. If there is any dispute about who was first to flip two Pottery, priority is determined by turn order."),
                'nrOfCardsToFlip' => 2
            ],
        ]
    ],
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


