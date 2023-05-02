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
 * location -> current die location possible values: player_area, exhausted, treasure_card_<card-id>
 * location_arg -> current die value
 */
class CryptServantDice extends APP_DbObject
{
    private $selectColumnList;
    protected $game;

    public function __construct($game)
    {
        $this->game = $game;
        $this->selectColumnList = "card_id as id, card_type as type, card_type_arg as type_arg, card_location as location, card_location_arg as location_arg, card_effort as effort"; // NOI18N
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

    public function getAllServantDice($playerId) {
        $sql = "SELECT ".$this->selectColumnList." FROM servant_dice WHERE card_type = '".$playerId."'";
        return self::getObjectListFromDB($sql);
    }

    public function getServantDie($id) {
        $sql = "SELECT ".$this->selectColumnList." FROM servant_dice WHERE card_id = '".$id."'";
        return self::getObjectFromDB($sql);
    }

    public function getServantDiceOnTreasureCards($playerId) {
        $sql = "SELECT ".$this->selectColumnList." FROM servant_dice WHERE card_type = '".$playerId."' AND card_location LIKE 'treasure_card_%'";
        return self::getObjectListFromDB($sql);
    }

    public function getServantDiceInPlayerArea($playerId) {
        $sql = "SELECT ".$this->selectColumnList." FROM servant_dice WHERE card_type = '".$playerId."' AND card_location = 'player_area'";
        return self::getCollectionFromDB($sql);
    }

    public function getServantDiceInExhaustedArea($playerId) {
        $sql = "SELECT ".$this->selectColumnList." FROM servant_dice WHERE card_type = '".$playerId."' AND card_location = 'exhausted'";
        return self::getObjectListFromDB($sql);
    }
    public function getServantDiceInExhaustedAreaWithEffortValue($playerId) {
        $sql = "SELECT ".$this->selectColumnList." FROM servant_dice WHERE card_type = '".$playerId."' AND card_location = 'exhausted' AND card_effort IS NOT NULL";
        return self::getObjectListFromDB($sql);
    }

    public function getServantDiceOnTreasureCard($treasureCardId) {
        $sql = "SELECT ".$this->selectColumnList." FROM servant_dice WHERE card_location LIKE 'treasure_card_" .$treasureCardId."'";
        return self::getObjectListFromDB($sql);
    }

    public function getServantDiceForReRoll($playerId) {
        $sql = "SELECT ".$this->selectColumnList." FROM servant_dice WHERE card_type = '".$playerId."' AND card_location = 'exhausted' AND card_effort IS NOT NULL";
        return self::getObjectListFromDB($sql);
    }

    public function moveServantDiceToTreasureCardWithValue($id, $treasureCardId, $dieValue) {
        $this->game->servant_dice->moveCard($id, 'treasure_card_' .$treasureCardId, $dieValue);
        self::DbQuery("UPDATE servant_dice SET card_effort=".$dieValue." WHERE card_id = ".$id);
        return $this->game->servant_dice->getCard($id);
    }

    public function resetAllEffortValues() {
        self::DbQuery("UPDATE servant_dice SET card_effort = null");
    }

    public function exhaustServantDie($id) {
        self::DbQuery("UPDATE servant_dice SET card_location='exhausted' WHERE card_id = ".$id);
        $this->game->scoreManager->updateTotalScore($this->getServantDie($id)['type']);
    }

    public function recoverServantDice($dieIds) {
        foreach ($dieIds as $dieId) {
            $this->recoverServantDie($dieId);
        }
        if (sizeof($dieIds) > 0) {
            $this->game->scoreManager->updateTotalScore($this->getServantDie(reset($dieIds))['type']);
        }
    }

    public function recoverServantDie($id) {
        self::DbQuery("UPDATE servant_dice SET card_location='player_area' WHERE card_id = ".$id);
    }

    public function setDieValue($id, $dieValue) {
        self::DbQuery("UPDATE servant_dice SET card_location_arg=".$dieValue." WHERE card_id = ".$id);
    }
}
