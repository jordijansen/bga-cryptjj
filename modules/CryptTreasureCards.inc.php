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
 * CryptTreasureCards.inc.php
 *
 * Handles the treasure cards deck, locations and states
 *
 */

class CryptTreasureCards extends APP_DbObject
{
    protected $game;

    public function __construct($game)
    {
        $this->game = $game;
    }

    public function createInitialTreasureCardsDeck($players)
    {
        // Create treasure cards deck based on player count
        // For a 1 or 2 player game the deck contains 1 copy of each treasure type and value
        // For a 3 player game the deck contains 2 copy of each treasure type values 2 & 3 and 1 copy of each treasure type values 1 & 4
        // For a 4 player game the deck contains 2 copies of each treasure type and value
        $cards = array();
        foreach( $this->game->treasure_types as $treasure_type => $name ) // jewelery, manuscript, remains, etc.
        {
            foreach ($this->game->treasure_values as $value) { //  1, 2, 3, 4
                if (sizeof($players) == 1 || sizeof($players) == 2) {
                    $cards[] = array( 'type' => $treasure_type, 'type_arg' => $value, 'nbr' => 1);
                } else if (sizeof($players) == 3) {
                    if ($value == 2 || $value == 3) {
                        $cards[] = array( 'type' => $treasure_type, 'type_arg' => $value, 'nbr' => 2);
                    } else {
                        $cards[] = array( 'type' => $treasure_type, 'type_arg' => $value, 'nbr' => 1);
                    }
                } else if (sizeof($players) == 4) {
                    $cards[] = array( 'type' => $treasure_type, 'type_arg' => $value, 'nbr' => 2);
                }
            }
        }

        // Since some cards are face-down, some information needs to be secret until flipped face-up. We shuffle the cards before creating the deck so the ids of the cards are in random order
        // This way the ids of the cards are different each time played and players can't guess the real value of cards.
        shuffle($cards);
        $this->game->treasure_cards->createCards( $cards, 'deck' );
        $this->game->treasure_cards->shuffle( 'deck' );
    }

    public function drawTreasureCardsForDisplay($players)
    {
        // Fill the treasure card display with cards.
        // Afterwards the display is filled with a number of face-up and face-down treasure cards based on the player count
        $faceUpCards = self::determineNumberOfFaceUpTreasureCards(sizeof($players));
        $faceDownCards = self::determineNumberOfFaceDownTreasureCards(sizeof($players));

        for ($i = 0; $i < $faceUpCards; $i++) {
            $this->game->treasure_cards->pickCardsForLocation(1, 'deck', 'display', $i);
        }
        $this->flipCardsFaceUp($this->getTreasureCardsInDisplay());
        for ($i = $faceUpCards; $i < $faceUpCards + $faceDownCards; $i++) {
            $this->game->treasure_cards->pickCardsForLocation(1, 'deck', 'display', $i);
        }
    }

    public function flipCardsFaceUp($cards) {
        self::debug('before');
        $ids = join(", ", array_map(function($card) { return $card['id'];}, $cards));
        self::debug($ids);
        self::DbQuery("UPDATE treasure_cards SET card_face_up=1 WHERE card_id in (".$ids.")");
    }

    public function getTreasureCardsInDisplay() {
        $sql = "SELECT card_id as id,
                       card_type as type,
                       CASE
                        WHEN card_face_up = 1 THEN card_type_arg
                        ELSE 'back'
                       END as value
                FROM treasure_cards
                WHERE card_location = 'display' 
                ORDER BY card_location_arg ASC";

        return self::getObjectListFromDB($sql);
    }

    function determineNumberOfFaceUpTreasureCards($playerCount) {
        switch ($playerCount) {
            case 1:
            case 2:
                 return 2;
            case 3:
                return 3;
            case 4:
                return 4;
        }
    }

    function determineNumberOfFaceDownTreasureCards($playerCount) {
        switch ($playerCount) {
            case 1:
            case 3:
            case 2:
                return 1;
            case 4:
                return 2;
        }
    }
}
