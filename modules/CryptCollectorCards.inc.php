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

class CryptCollectorCards extends APP_DbObject
{
    protected $game;

    public function __construct($game)
    {
        $this->game = $game;
    }

    public function createCollectorCards($options)
    {
        $sql = "INSERT INTO collectors (id, treasure_type, side, ability_type, nr_of_cards_to_flip) VALUES ";
        $values = array();

        foreach( $this->game->collectors as $treasure_type => $collector )
        {
            $side = $this->determineSide($options);
            $id = $treasure_type . "-" .$side;
            $values[] = "('".$id."','".$treasure_type."','".$side."','".$collector['sides'][$side]['type']."', ".$collector['sides'][$side]['nrOfCardsToFlip'].")";
        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );
    }

    public function getCollectors() {
       $results = self::getObjectListFromDB("SELECT * FROM collectors");
       foreach ($results as $index => $collector) {
           $results[$index]['name_translated'] = $this->game->collectors[$collector['treasure_type']]['name'];
           $results[$index]['description_translated'] = $this->game->collectors[$collector['treasure_type']]['sides'][$collector['side']]['description'];
       }
       return $results;
    }

    public function getCollector($id) {
        $collector = self::getObjectFromDB("SELECT * FROM collectors WHERE id = '".$id."'");
        $collector['name_translated'] = $this->game->collectors[$collector['treasure_type']]['name'];
        $collector['description_translated'] = $this->game->collectors[$collector['treasure_type']]['sides'][$collector['side']]['description'];
        return $collector;
    }

    public function useCollector($playerId, $collector, $treasureCards) {
        $flippedTreasureCards = [];
        foreach ($treasureCards as $treasureCard) {
            if ($collector['treasure_type'] === $treasureCard['type']) {
                $this->game->treasureCardsManager->flipCard($treasureCard['id']);
                // We always use the playerId used here, since after flipping it, all information is public to everybody
                $flippedTreasureCards[] = $this->game->treasureCardsManager->getTreasureCard($treasureCard['id'], $playerId);
            } else {
                throw new BgaUserException("The provided Treasure Cards are not of type " . $collector['treasure_type']);
            }
        }

        if ($collector['id'] === 'remains-A') {
            // ANY_TIME recover 1 exhausted servant Dice
            $exhaustedServantDice = $this->game->servantDiceManager->getServantDiceInExhaustedArea($playerId);
            $servantDieId = reset($exhaustedServantDice)['id'];
            $this->game->servantDiceManager->recoverServantDie($servantDieId);

            $recoveredServantDice = [];
            $recoveredServantDice[] = $this->game->servantDiceManager->getServantDie($servantDieId);
            $this->game->notificationsManager->notifyCollectorUsed($playerId, $collector, $flippedTreasureCards);
            $this->game->notificationsManager->notifyServantDiceRecovered($playerId, $recoveredServantDice, true);
        } else if ($collector['id'] === 'manuscript-B') {
            // ANY_TIME reveal cards in display
            $treasureCardsInDisplay = $this->game->treasureCardsManager->getAllTreasureCardsInDisplay(true);
            $this->game->notificationsManager->notifyCollectorUsed($playerId, $collector, $flippedTreasureCards);
            $this->game->notificationsManager->notifyFaceDownDisplayCardsRevealed($playerId, $treasureCardsInDisplay);
            $this->setHasUsedManuscriptBThisRound($playerId);
        }
    }

    private function setHasUsedManuscriptBThisRound($playerId) {
        self::DbQuery("UPDATE player SET has_used_manuscript_b_this_round=1 WHERE player_id = " .$playerId);
    }

    public function hasUsedManuscriptBThisRound($playerId) {
        return self::getUniqueValueFromDB("SELECT has_used_manuscript_b_this_round FROM player WHERE player_id = " .$playerId) == '1';
    }

    private function determineSide($options)
    {
        if ($options[OPTION_COLLECTORS] == OPTION_COLLECTORS_A) {
            return 'A';
        } else if ($options[OPTION_COLLECTORS] == OPTION_COLLECTORS_B) {
            return 'B';
        } else {
            return bga_rand(1, 2) === 1 ? 'A' : 'B';
        }
    }
}
