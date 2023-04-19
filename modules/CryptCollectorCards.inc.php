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
        $sql = "INSERT INTO collectors (id, treasure_type, side, ability_type) VALUES ";
        $values = array();

        foreach( $this->game->collectors as $treasure_type => $collector )
        {
            $side = $this->determineSide($options);
            $id = $treasure_type . "-" .$side;
            $values[] = "('".$id."','".$treasure_type."','".$side."','".$collector['sides'][$side]['type']."')";

        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );
    }

    public function getCollectors() {
       $results = self::getObjectListFromDB("SELECT * FROM collectors");
       foreach ($results as $index => $collector) {
           $results[$index]['description_translated'] = $this->game->collectors[$collector['treasure_type']]['sides'][$collector['side']]['description'];
       }
       return $results;
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
