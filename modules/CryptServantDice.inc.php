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
 * CryptServantDice.inc.php
 *
 * Handles the servant dice, locations and states
 *
 */

class CryptServantDice extends APP_DbObject
{
    protected $game;

    public function __construct($game)
    {
        $this->game = $game;
    }

    public function createServantDice()
    {
        // Create treasure cards deck based on player count
        // For a 1 or 2 player game the deck contains 1 copy of each treasure type and value
        // For a 3 player game the deck contains 2 copy of each treasure type values 2 & 3 and 1 copy of each treasure type values 1 & 4
        // For a 4 player game the deck contains 2 copies of each treasure type and value
        $dice = array();
        $players = $this->game->loadPlayersBasicInfos();
        foreach( $players as $player_id => $player )
        {
            $dice[] = array( 'type' => $player_id, 'type_arg' => $player['player_color'], 'nbr' => 3);
        }

        $this->game->servant_dice->createCards( $dice, 'player_area', 1);
    }

    public function getAllServantDice($player_id) {
        return $this->game->servant_dice->getCardsOfType($player_id);
    }


}
