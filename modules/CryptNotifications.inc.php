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

    public function notifyServantDiceRecovered($playerId, $recoveredServantDice, $isPlayerInitiated) {
        $playerPerformingAction = $this->game->getPlayer($playerId);
        $message = $isPlayerInitiated ? clienttranslate( '${player_name} recovers servant dice') : clienttranslate('${player_name} has no servant dice remaining, recovering servant dice');

        $players = $this->game->loadPlayersBasicInfos();
        foreach( $players as $id => $player )
        {
            $this->game->notifyPlayer($id, 'servantDiceRecovered', $message, array(
                'playerId' => $playerId,
                'player_name' => $playerPerformingAction['player_name'],
                'player_score' => $this->game->scoreManager->getTotalScore($playerId, $id == $playerId),
                'recoveredServantDice' => $recoveredServantDice
            ));
        }
    }

    public function notifyTreasureCardDiscarded($treasureCard) {
        $this->game->notifyAllPlayers('treasureCardDiscarded', clienttranslate( '${treasureCard.type} is discarded'), array(
            'treasureCard' => $treasureCard
        ));
    }

    public function notifyTreasureCardCollected($playerId, $treasureCardId, $rolledServantDice) {
        $playerPerformingAction = $this->game->getPlayer($playerId);

        $players = $this->game->loadPlayersBasicInfos();
        foreach( $players as $id => $player )
        {
            $this->game->notifyPlayer($id, 'treasureCardCollected', $id == $playerId ? clienttranslate( 'You collect ${treasureCard.type}') : clienttranslate( '${player_name} collects ${treasureCard.type}'), array(
                'playerId' => $playerPerformingAction['player_id'],
                'player_name' => $playerPerformingAction['player_name'],
                'player_score' => $this->game->scoreManager->getTotalScore($playerPerformingAction['player_id'], $id == $playerId),
                'treasureCard' => $this->game->treasureCardsManager->getTreasureCard($treasureCardId, $id),
                'rolledServantDice' => $rolledServantDice
            ));
        }
    }

    public function notifyFaceDownDisplayCardsRevealed($playerId, $treasureCardsInDisplay) {
        $playerPerformingAction = $this->game->getPlayer($playerId);

        $players = $this->game->loadPlayersBasicInfos();
        foreach( $players as $id => $player )
        {
            $this->game->notifyPlayer($id, 'faceDownDisplayCardsRevealed', $id == $playerId ? clienttranslate( 'You reveal face down treasure cards') : clienttranslate( '${player_name} looks at face down treasure cards in display'), array(
                'playerId' => $playerPerformingAction['player_id'],
                'player_name' => $playerPerformingAction['player_name'],
                'treasureCards' => $id == $playerId ? $treasureCardsInDisplay : null
            ));
        }
    }

    public function notifyTorchCardsPassed() {
        $players = $this->game->loadPlayersBasicInfos();
        foreach( $players as $id => $player )
        {
            if ($this->game->playerManager->hasLeaderCard($id)) {
                $this->game->notifyAllPlayers('leaderCardPassed', clienttranslate( '${player_name} gains the leader card'), array(
                    'player_id' => $id,
                    'player_name' => $player['player_name'],
                ));
            }
            if ($this->game->playerManager->hasLightsOutCard($id)) {
                $this->game->notifyAllPlayers('lightsOutCardPassed', clienttranslate( '${player_name} gains the lights out card'), array(
                    'player_id' => $id,
                    'player_name' => $player['player_name'],
                ));
            }
        }
    }

    public function notifyCollectorUsed($playerId, $collector, $flippedTreasureCards) {
        $playerPerformingAction = $this->game->getPlayer($playerId);

        $players = $this->game->loadPlayersBasicInfos();
        foreach( $players as $id => $player )
        {
            $this->game->notifyPlayer($id, 'collectorUsed', clienttranslate( '${player_name} activates ${collector.name_translated}'), array(
                'playerId' => $playerId,
                'player_name' => $playerPerformingAction['player_name'],
                'player_score' => $this->game->scoreManager->getTotalScore($playerId, $id == $playerId),
                'collector' => $collector,
                'flippedTreasureCards' => $flippedTreasureCards
            ));
        }
    }

    public function servantDieReRolled($playerId, $servantDie, $originalValue, $rolledValue, $exhausted) {
        $player = $this->game->getPlayer($playerId);

        $additionalText = clienttranslate( 'which is equal or higher than ${effort}, so it is recovered');
        if ($exhausted) {
            $additionalText = clienttranslate( 'which is lower than ${effort}, so it remains exhausted');
        }
        $this->game->notifyAllPlayers('servantDieReRolled', clienttranslate( '${player_name} re-rolls ${originalValue} into a ${rolledValue} ') .$additionalText, array(
            'playerId' => $playerId,
            'player_name' => $player['player_name'],
            'servantDie' => $servantDie,
            'originalValue' => $originalValue,
            'rolledValue' => $rolledValue,
            'effort' => $servantDie['effort'],
            'exhausted' => $exhausted
        ));
    }

    public function notifyTieBreakerRolled($playerId, $rolledValues) {
        $player = $this->game->getPlayer($playerId);
        $this->game->notifyAllPlayers('tieBreakerRolled', clienttranslate( '${player_name} is tied and rolls [dice] to break tie') .array_sum($rolledValues), array(
            'playerId' => $playerId,
            'player_name' => $player['player_name'],
            'rolledValues' => $rolledValues
        ));
    }

    public function notifyFinalScoring($finalScoring) {
        $playersRow = [ '' ];
        $treasureCardCoinsRow = [ clienttranslate("Treasure Card Coins")];
        $unExhaustedServantDiceRow = [ clienttranslate("Un-exhausted Servant Dice")];
        $totalScoreRow = [clienttranslate("Total")];

        $players = $this->game->loadPlayersBasicInfos();
        foreach( $players as $playerId => $player )    {
            $playersRow[] = [ 'str' => '${player_name}',
                'args' => [ 'player_name' => $player['player_name'] ],
                'type' => 'header'
            ];
            $treasureCardCoinsRow[] = $finalScoring[$playerId]['treasureCardCoins'];
            $unExhaustedServantDiceRow[] = $finalScoring[$playerId]['unExhaustedServantDice'];
            $totalScoreRow[] = $finalScoring[$playerId]['totalScore'];
        }

        $table = [$playersRow, $treasureCardCoinsRow, $unExhaustedServantDiceRow];

        foreach( $this->game->treasure_types as $treasureTypeId => $treasureType) { // jewelery, manuscript, remains, etc.
            $treasureTypeRow = [$treasureType['name']." ".clienttranslate("Collector")];
            foreach( $players as $playerId => $player ) {
                if (array_key_exists($treasureTypeId, $finalScoring[$playerId]['collectors'])) {
                    $collectorScore = $finalScoring[$playerId]['collectors'][$treasureTypeId];
                    if (isset($collectorScore)) {
                        $treasureTypeRow[] = $finalScoring[$playerId]['collectors'][$treasureTypeId];
                    }
                }
            }
            if (sizeof($treasureTypeRow) > 1) {
                $table[] = $treasureTypeRow;
            }
        }

        $table[] = $totalScoreRow;

        self::trace(json_encode($table));
        $this->game->notifyAllPlayers( "tableWindow", '', array(
            "id" => 'finalScoring',
            "title" => clienttranslate("End Game Scoring"),
            "table" => $table,
            "closing" => clienttranslate( "Close" )
        ));
    }

    public function notifyAllCardsFlipped($treasureCards = []) {
        $this->game->notifyAllPlayers('allCardsFlipped', '', array(
            'treasureCards' => $treasureCards
        ));
    }
}
