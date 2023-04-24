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

    public function getAvailableCollectors($playerId, $abilityType) {
        $results = self::getObjectListFromDB("SELECT * 
                FROM collectors c 
                WHERE c.ability_type = '".$abilityType."'
                AND EXISTS (SELECT COUNT(1) 
                            FROM treasure_cards tc
                            WHERE tc.card_type = c.treasure_type
                            AND tc.card_location = concat('player_area_', ".$playerId.")
                            AND tc.card_flipped = 0
                            HAVING COUNT(1) >= c.nr_of_cards_to_flip)");
        foreach ($results as $index => $collector) {
            $results[$index]['name_translated'] = $this->game->collectors[$collector['treasure_type']]['name'];
            $results[$index]['description_translated'] = $this->game->collectors[$collector['treasure_type']]['sides'][$collector['side']]['description'];
        }
        return $results;
    }

    public function useCollector($playerId, $collector, $treasureCardsToFlip, $treasureCardsSelected) {
        $flippedTreasureCards = [];
        foreach ($treasureCardsToFlip as $treasureCard) {
            if ($collector['treasure_type'] !== $treasureCard['type']) {
                throw new BgaUserException("The provided Treasure Card is not of type " . $collector['treasure_type']);
            }
            if ($treasureCard['flipped'] !== '0') {
                throw new BgaUserException("The provided Treasure Card is already flipped");
            }

            $this->game->treasureCardsManager->flipCard($treasureCard['id']);
            // We always use the playerId used here, since after flipping it, all information is public to everybody
            $flippedTreasureCards[] = $this->game->treasureCardsManager->getTreasureCard($treasureCard['id'], $playerId);
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
            if ($this->hasUsedManuscriptBThisRound($playerId)) {
                throw new BgaUserException("You've already revealed the face down treasure cards this round");
            }
            $this->setHasUsedManuscriptBThisRound($playerId);
            $treasureCardsInDisplay = $this->game->treasureCardsManager->getTreasureCardsInDisplayForPlayer($playerId);
            $this->game->notificationsManager->notifyCollectorUsed($playerId, $collector, $flippedTreasureCards);
            $this->game->notificationsManager->notifyFaceDownDisplayCardsRevealed($playerId, $treasureCardsInDisplay);
        } else if ($collector['id'] === 'pottery-B') {
            // BEFORE_CLAIM_PHASE collect face-up card from display
            if (isset($treasureCardsSelected) && sizeof($treasureCardsSelected) != 1) {
                throw new BgaUserException("You need to select 1 treasure card");
            }
            $treasureCard = $this->game->treasureCardsManager->getTreasureCard(reset($treasureCardsSelected), $playerId);
            if ($treasureCard['location'] !== 'display') {
                throw new BgaUserException("You need to select a treasure card in the display");
            }
            self::trace(json_encode($treasureCard));

            if ($treasureCard['face_up'] !== '0') {
                throw new BgaUserException("You need to select a face-down treasure card");
            }

            $this->game->treasureCardsManager->collectTreasureCard($playerId, $treasureCard['id']);
            $this->game->notificationsManager->notifyCollectorUsed($playerId, $collector, $flippedTreasureCards);
            $this->game->notificationsManager->notifyTreasureCardCollected($playerId, $treasureCard['id'], []);
            if (sizeof($this->getAvailableCollectors($playerId, COLLECTOR_BEFORE_CLAIM_PHASE)) < 1) {
                // Auto end turn if no more collectors can be activated
                $this->game->gamestate->nextState(STATE_END_BEFORE_CLAIM_PHASE_ACTIVATE_COLLECTORS);
            }
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
