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
 * id -> card id
 * type -> jewelery, manuscript, remains, etc.
 * type_arg -> value of the card
 * location -> deck, display, discard, player_area_<player_id>
 * location_arg -> order within the location
 * card_face_up -> whether the card has been face-up, if it is the front of the card is public info.
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
//                    $cards[] = array( 'type' => $treasure_type, 'type_arg' => $value, 'nbr' => 1); // TODO REMOVE BECAUSE THIS SHORTENS THE GAME
                }
            }
        }

        // Since some cards are face-down, some information needs to be secret until flipped face-up. We shuffle the cards before creating the deck so the ids of the cards are in random order
        // This way the ids of the cards are different each time played and players can't guess the real value of cards.
        shuffle($cards);
        $this->game->treasure_cards->createCards( $cards, 'deck' );
        $this->game->treasure_cards->shuffle( 'deck' );
    }

    public function drawTreasureCardsForDisplay($playerCount)
    {
        // Fill the treasure card display with cards.
        // Afterwards the display is filled with a number of face-up and face-down treasure cards based on the player count
        $faceUpCards = self::determineNumberOfFaceUpTreasureCards($playerCount);
        $faceDownCards = self::determineNumberOfFaceDownTreasureCards($playerCount);

        for ($i = 0; $i < $faceUpCards; $i++) {
            $this->game->treasure_cards->pickCardsForLocation(1, 'deck', 'display', $i);
        }
        $this->setCardsFaceUpTrue($this->getAllTreasureCardsInDisplay());
        for ($i = $faceUpCards; $i < $faceUpCards + $faceDownCards; $i++) {
            $this->game->treasure_cards->pickCardsForLocation(1, 'deck', 'display', $i);
        }
    }

    public function setCardsFaceUpTrue($cards) {
        $ids = join(", ", array_map(function($card) { return $card['id'];}, $cards));
        self::DbQuery("UPDATE treasure_cards SET card_face_up=1 WHERE card_id in (".$ids.")");
    }

    public function getAllTreasureCardsInPlay($playerId) {
        $sql = $this->baseTreasureCardQuery($playerId) ." WHERE card_location != 'deck'
                ORDER BY card_location_arg ASC";

        return self::getObjectListFromDB($sql);
    }

    public function findByPlayerIdAndTypeAndUnFlipped($playerId, $type) {
        $sql = $this->baseTreasureCardQuery($playerId) ." 
                WHERE card_location = 'player_area_".$playerId."' AND card_type = '".$type."' AND card_flipped = 0
                ORDER BY card_face_up DESC";

        return self::getObjectListFromDB($sql);
    }

    public function findByPlayerIdAndType($playerId, $type) {
        $sql = $this->baseTreasureCardQuery($playerId) ." 
                WHERE card_location = 'player_area_".$playerId."' AND card_type = '".$type."'
                ORDER BY card_face_up DESC";

        return self::getObjectListFromDB($sql);
    }

    public function findByPlayerId($playerId) {
        $sql = $this->baseTreasureCardQuery($playerId) ." 
                WHERE card_location = 'player_area_".$playerId."' 
                ORDER BY card_face_up DESC";

        return self::getObjectListFromDB($sql);
    }

    public function getTreasureCard($cardId, $playerId) {
        $sql = $this->baseTreasureCardQuery($playerId) ." WHERE card_id = " .$cardId;

        return self::getObjectFromDB($sql);
    }

    public function getTreasureCardsInDisplayForPlayer($playerId) {
        $sql = $this->baseTreasureCardQuery($playerId) ." WHERE card_location = 'display' ORDER BY card_location_arg ASC";

        return self::getObjectListFromDB($sql);
    }

    public function getAllTreasureCardsInDisplay() {
        $sql = "SELECT card_id as id,
                       card_type as type,
                       CASE
                        WHEN card_face_up = 1 THEN card_type_arg
                        ELSE 'back'
                       END as value,
                       card_location as location,
                       card_face_up as face_up,
                       card_flipped as flipped
                FROM treasure_cards
                WHERE card_location = 'display'
                ORDER BY card_location_arg ASC";

        return self::getObjectListFromDB($sql);
    }

    public function discardTreasureCard($treasureCardId) {
        $this->game->treasure_cards->moveCard($treasureCardId, 'discard');
    }

    public function collectTreasureCard($playerId, $treasureCardId) {
        $this->game->treasure_cards->moveCard($treasureCardId, 'player_area_' .$playerId);
        $this->game->scoreManager->updateTotalScore($playerId);
    }

    public function countCardsInDeck() {
        return $this->game->treasure_cards->countCardInLocation('deck');
    }

    public function countCards() {
        return self::getUniqueValueFromDB("SELECT count(1) FROM treasure_cards");
    }

    public function flipCard($treasureCardId) {
        self::DbQuery("UPDATE treasure_cards SET card_flipped=1, card_face_up=1 WHERE card_id = ".$treasureCardId);
    }

    public function flipAllCardsInPlayerAreas() {
        self::DbQuery("UPDATE treasure_cards SET card_flipped=1, card_face_up=1 WHERE card_location LIKE 'player_area_%'");
        $sql = $this->baseTreasureCardQuery('999999999') ." 
                WHERE card_location LIKE 'player_area_%'";

        return self::getObjectListFromDB($sql);
    }

    function baseTreasureCardQuery($playerId) {
        return "SELECT card_id as id,
                       card_type as type,
                       CASE
                        WHEN card_face_up = 1 OR card_location = 'player_area_".$playerId."' 
                        OR (SELECT has_used_manuscript_b_this_round FROM player WHERE player_id = ".$playerId.") = 1 THEN card_type_arg
                        ELSE 'back'
                       END as value,
                       card_location as location,
                       card_face_up as face_up,
                       card_flipped as flipped
                FROM treasure_cards";
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
