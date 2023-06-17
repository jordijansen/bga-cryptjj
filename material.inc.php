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
 * material.inc.php
 *
 * Crypt game material description
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
        'name' => clienttranslate("Idol Collector"),
        'sides' => [
            'A' => [
                'type' => COLLECTOR_COLLECT_PHASE,
                'description' => clienttranslate("During the Collect phase, flip an Idol face-up to re-roll one of your dice. An Idol may be flipped to re-roll on the same turn that it was collected."),
                'nrOfCardsToFlip' => 1
            ],
            'B' => [
                'type' => COLLECTOR_END_GAME,
                'description' => clienttranslate("The first player to collect two Idols flips them face-up and scores 5 bonus Coins at game end. If multiple players collect two Idols on the same turn, each flips their Idols and scores 5 bonus Coins. All subsequent players who collect two or more Idols keep them face-down and score 2 Coins. Cards are flipped face-up as soon as a player meets the requirements."),
                'nrOfCardsToFlip' => 2
            ],
        ]
    ],
    'jewelery' => [
        'name' => clienttranslate("Jewelery Collector"),
        'sides' => [
            'A' => [
                'type' => COLLECTOR_END_GAME,
                'description' => clienttranslate("At game end, players with two or more Jewelry score their highest valued Jewelry twice. For example: if you have two jewelery cards valued 2 and 3 respectively, this collector scores you an additional 3 coins during final scoring."),
                'nrOfCardsToFlip' => 0
            ],
            'B' => [
                'type' => COLLECTOR_END_GAME,
                'description' => clienttranslate("At game end, all players score 1 bonus Coin for each Jewelry in their collection. For example: if you have two jewelery cards, this collector scores you an additional 2 coins during final scoring."),
                'nrOfCardsToFlip' => 0
            ],
        ]
    ],
    'manuscript' => [
        'name' => clienttranslate("Manuscript Collector"),
        'sides' => [
            'A' => [
                'type' => COLLECTOR_END_GAME,
                'description' => clienttranslate("At game end, players with two or more Manuscripts score each of their Manuscripts as 4 Coins instead of the value printed on the cards. For example: if you have two manuscript cards values 2 and 3 respectively, this collector scores you an additional 3 (2 + 1) coins during final scoring."),
                'nrOfCardsToFlip' => 0
            ],
            'B' => [
                'type' => COLLECTOR_ANY_TIME,
                'description' => clienttranslate("At any time, flip one Manuscript face-up to secretly view all the face-down card(s) in the Crypt."),
                'nrOfCardsToFlip' => 1
            ],
        ]
    ],
    'tapestry' => [
        'name' => clienttranslate("Tapestry Collector"),
        'sides' => [
            'A' => [
                'type' => COLLECTOR_END_GAME,
                'description' => clienttranslate("At game end, the player whose combined Tapestries are worth the most Coins scores 5 bonus Coins. Tied players each score the full bonus. For example: player X has a combined value of 5 and player Y has a combined value of 6, player Y scores an additional 5 coins for this collector during final scoring."),
                'nrOfCardsToFlip' => 0
            ],
            'B' => [
                'type' => COLLECTOR_END_GAME,
                'description' => clienttranslate("At game end, if only one player has three or more Tapestries, they score 7 bonus Coins. If more than one player collects three or more Tapestries, each player scores 4 bonus Coins. For example: player X has 2 Tapestry cards and player Y has 3 Tapestry Cards, player Y scores an additional 7 coins during final scoring."),
                'nrOfCardsToFlip' => 0
            ],
        ]
    ],
    'remains' => [
        'name' => clienttranslate("Remains Collector"),
        'sides' => [
            'A' => [
                'type' => COLLECTOR_ANY_TIME,
                'description' => clienttranslate("At any time, flip two Remains face-up to recover one exhausted Servant die."),
                'nrOfCardsToFlip' => 2
            ],
            'B' => [
                'type' => COLLECTOR_END_GAME,
                'description' => clienttranslate("At game end, players with four or more Remains score 10 bonus Coins."),
                'nrOfCardsToFlip' => 0
            ],
        ]
    ],
    'pottery' => [
        'name' => clienttranslate("Pottery Collector"),
        'sides' => [
            'A' => [
                'type' => COLLECTOR_END_GAME,
                'description' => clienttranslate("At game end, players with two Pottery score 2 bonus Coins, three Pottery score 4 bonus Coins, and four or more Pottery score 8 bonus Coins."),
                'nrOfCardsToFlip' => 0
            ],
            'B' => [
                'type' => COLLECTOR_BEFORE_CLAIM_PHASE,
                'description' => clienttranslate("Before the Claim phase, flip two Pottery face-up to take a face-down card from the Crypt. If there is any dispute about who was first to flip two Pottery, priority is determined by turn order."),
                'nrOfCardsToFlip' => 2
            ],
        ]
    ],
];

$this->treasure_types = [
    'tapestry' => [
        'name' => clienttranslate("tapestry")
    ],
    'idol' => [
        'name' => clienttranslate("idol")
    ],
    'remains' => [
        'name' => clienttranslate("remains")
    ],
    'pottery' => [
        'name' => clienttranslate("pottery")
    ],
    'jewelery' => [
        'name' => clienttranslate("jewelery")
    ],
    'manuscript' => [
        'name' => clienttranslate("manuscript")
    ]
];

$this->treasure_values = [
    1, 2, 3, 4
];

$this->card_types = [
    'collector' => [
        'name' => clienttranslate("Collector")
    ],
    'treasure' => [
        'name' => clienttranslate("Treasure")
    ]
];


