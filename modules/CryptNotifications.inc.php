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

class CryptNotifications extends APP_DbObject
{
    protected $game;

    public function __construct($game)
    {
        $this->game = $game;
    }

    public function notifyTreasureCardBumped($playerId, $treasureCard, $servantDiceOnTreasureCard) {
        $player = $this->game->getPlayer($playerId);
        $bumpedPlayer = $this->game->getPlayer(array_values($servantDiceOnTreasureCard)[0]['type']);

        $this->game->notifyAllPlayers('treasureCardBumped', clienttranslate( '${player_name1} bumps ${player_name2} from ${treasureCard.type}'), array(
            'playerId' => $playerId,
            'player_name1' => $player['player_name'],
            'player_name2' => $bumpedPlayer['player_name'],
            'treasureCard' => $treasureCard,
            'bumpedServantDice' => $servantDiceOnTreasureCard
        ));
    }

    public function notifyTreasureCardClaimed($playerId, $treasureCard, $servantDice) {
        $player = $this->game->getPlayer($playerId);

        $this->game->notifyAllPlayers('treasureCardClaimed', clienttranslate( '${player_name} claims ${treasureCard.type}'), array(
            'playerId' => $playerId,
            'player_name' => $player['player_name'],
            'treasureCard' => $treasureCard,
            'servantDice' => $servantDice
        ));
    }

    public function notifyServantDiceRecovered($playerId, $playerName, $recoveredServantDice) {
        $this->game->notifyAllPlayers('servantDiceRecovered', clienttranslate( '${player_name} recovers servant dice'), array(
            'playerId' => $playerId,
            'player_name' => $playerName,
            'recoveredServantDice' => $recoveredServantDice
        ));
    }

    public function notifyTreasureCardDiscarded($treasureCard) {
        $this->game->notifyAllPlayers('treasureCardDiscarded', clienttranslate( '${treasureCard.type} is discarded'), array(
            'treasureCard' => $treasureCard
        ));
    }

    public function notifyTreasureCardCollected($playerId, $treasureCardId, $rolledServantDice) {
        $this->game->notifyPlayer($playerId, 'treasureCardCollected', clienttranslate( 'You collect ${treasureCard.type}'), array(
            'playerId' => $playerId,
            'treasureCard' => $this->game->treasureCardsManager->getTreasureCard($treasureCardId, $playerId),
            'rolledServantDice' => $rolledServantDice
        ));

        $players = $this->game->loadPlayersBasicInfos();
        foreach( $players as $id => $player )
        {
            if ($id != $playerId) {
                $this->game->notifyPlayer($id, 'treasureCardCollected', clienttranslate( '${player_name} collects ${treasureCard.type}'), array(
                    'playerId' => $playerId,
                    'player_name' => $player['player_name'],
                    'treasureCard' => $this->game->treasureCardsManager->getTreasureCard($treasureCardId, $id),
                    'rolledServantDice' => $rolledServantDice
                ));
            }
        }
    }

    public function notifyTorchCardsPassed() {
        $players = $this->game->loadPlayersBasicInfos();
        foreach( $players as $id => $player )
        {
            if ($this->game->torchCardsManager->hasLeaderCard($id)) {
                $this->game->notifyAllPlayers('leaderCardPassed', clienttranslate( '${player_name} gains the leader card'), array(
                    'player_id' => $id,
                    'player_name' => $player['player_name'],
                ));
            }
            if ($this->game->torchCardsManager->hasLightsOutCard($id)) {
                $this->game->notifyAllPlayers('lightsOutCardPassed', clienttranslate( '${player_name} gains the lights out card'), array(
                    'player_id' => $id,
                    'player_name' => $player['player_name'],
                ));
            }
        }
    }
}
