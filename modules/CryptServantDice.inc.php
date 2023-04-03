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
 * id -> servant die id
 * type -> player id
 * type_arg -> player / die color
 * location -> current die location possible values: player_area, treasure_card_<card-id>
 * location_arg -> current die value
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

    public function getServantDiceInPlayerArea($playerId) {
        return $this->game->servant_dice->getCardsOfTypeInLocation($playerId, null, 'player_area', null);
    }

    public function moveServantDiceToTreasureCardWithValue($id, $treasureCardId, $dieValue) {
        $this->game->servant_dice->moveCard($id, 'treasure_card_' .$treasureCardId, $dieValue);
        return $this->game->servant_dice->getCard($id);
    }
}
