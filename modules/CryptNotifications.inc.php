<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Crypt implementation : © Jordi Jansen <thestartplayer@gmail.com>
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

    public function notifyTreasureCardBumped($playerId, $treasureCardId, $servantDiceOnTreasureCard) {
        $playerPerformingAction = $this->game->getPlayer($playerId);

        $treasureCard = $this->game->treasureCardsManager->getTreasureCardForPublic($treasureCardId);
        $this->game->notifyAllPlayers('treasureCardBumped', clienttranslate( '${player_name} bumps ${icon_dice} from ${icon_coin}${icon_treasure}'), array(
            'playerId' => $playerId,
            'player_name' => $playerPerformingAction['player_name'],
            'treasureCard' => $treasureCard,
            'bumpedServantDice' => $servantDiceOnTreasureCard,
            'icon_treasure' => $treasureCard['type'],
            'icon_coin' => $treasureCard['value'],
            'icon_dice' => $servantDiceOnTreasureCard
        ));
    }

    public function notifyTreasureCardClaimed($playerId, $treasureCardId, $servantDice) {
        $playerPerformingAction = $this->game->getPlayer($playerId);

        $treasureCard = $this->game->treasureCardsManager->getTreasureCardForPublic($treasureCardId);
        $this->game->notifyAllPlayers('treasureCardClaimed', clienttranslate( '${player_name} claims ${icon_coin}${icon_treasure} using ${icon_dice}'), array(
            'playerId' => $playerId,
            'player_name' => $playerPerformingAction['player_name'],
            'treasureCard' => $treasureCard,
            'servantDice' => $servantDice,
            'icon_treasure' => $treasureCard['type'],
            'icon_coin' => $treasureCard['value'],
            'icon_dice' => $servantDice
        ));
    }

    public function notifyServantDiceRecovered($playerId, $recoveredServantDice, $isPlayerInitiated) {
        $playerPerformingAction = $this->game->getPlayer($playerId);
        $message = $isPlayerInitiated ? clienttranslate( '${player_name} recovers ${icon_dice}') : clienttranslate('${player_name} has no servant dice remaining, recovers ${icon_dice}');

        $this->game->notifyAllPlayers('servantDiceRecovered', $message, array(
            'player_id' => $playerId,
            'player_name' => $playerPerformingAction['player_name'],
            'player_score' => $this->game->scoreManager->getTotalScore($playerId),
            'recoveredServantDice' => $recoveredServantDice,
            'icon_dice' => $recoveredServantDice
        ));
    }

    public function notifyTreasureCardDiscarded($treasureCardId) {
        $treasureCard = $this->game->treasureCardsManager->getTreasureCardForPublic($treasureCardId);
        $this->game->notifyAllPlayers('treasureCardDiscarded', clienttranslate( '${icon_coin}${icon_treasure} is discarded'), array(
            'treasureCard' => $treasureCard,
            'icon_treasure' => $treasureCard['type'],
            'icon_coin' => $treasureCard['value']
        ));
    }

    public function notifyTreasureCardCollected($playerId, $treasureCardId, $effort = null, $rolledServantDice = []) {
        $playerPerformingAction = $this->game->getPlayer($playerId);

        $treasureCard = $this->game->treasureCardsManager->getTreasureCardForPublic($treasureCardId);
        $logMessage = $effort != null ? clienttranslate('${player_name} collects ${icon_coin}${icon_treasure} with an effort of ${effort} and rolls ${icon_dice}') : clienttranslate('${player_name} collects ${icon_coin}${icon_treasure}');

        $this->game->notifyAllPlayers('treasureCardCollected', $logMessage, array(
            'player_id' => $playerPerformingAction['player_id'],
            'player_name' => $playerPerformingAction['player_name'],
            'player_score' => $this->game->scoreManager->getTotalScore($playerPerformingAction['player_id']),
            'treasureCard' => $treasureCard,
            'rolledServantDice' => $rolledServantDice,
            'effort'=> $effort,
            'icon_treasure' => $treasureCard['type'],
            'icon_coin' => $treasureCard['value'],
            'icon_dice' => $rolledServantDice
        ));

        $privateTreasureCard = $this->game->treasureCardsManager->getTreasureCard($treasureCardId, $playerId);
        $this->game->notifyPlayer($playerPerformingAction['player_id'], 'treasureCardCollectedPrivate', '', array(
            'treasureCard' => $privateTreasureCard
        ));

        $players = $this->game->loadPlayersBasicInfos();
        foreach( $players as $id => $player ) {
            if ($this->game->collectorCardsManager->hasUsedManuscriptBThisRound($id)) {
                $this->game->notifyPlayer($id, 'treasureCardCollectedPrivate', '', array(
                    'treasureCard' => $privateTreasureCard
                ));
            }
        }

    }

    public function notifyFaceDownDisplayCardsRevealed($playerId, $treasureCardsInDisplay) {
        $playerPerformingAction = $this->game->getPlayer($playerId);

        $this->game->notifyAllPlayers('faceDownDisplayCardsRevealed', clienttranslate( '${player_name} looks at face down treasure cards in display'), array(
            'playerId' => $playerPerformingAction['player_id'],
            'player_name' => $playerPerformingAction['player_name']
        ));

        $this->game->notifyPlayer($playerId, 'faceDownDisplayCardsRevealedPrivate', '', [
            'playerId' => $playerPerformingAction['player_id'],
            'treasureCards' => $treasureCardsInDisplay
        ]);
    }

    public function notifyTorchCardsPassed() {
        $players = $this->game->loadPlayersBasicInfos();
        foreach( $players as $id => $player )
        {
            if ($this->game->playerManager->hasLeaderCard($id)) {
                $this->game->notifyAllPlayers('leaderCardPassed', clienttranslate( '${player_name} gains the ${icon_torch} card'), array(
                    'player_id' => $id,
                    'player_name' => $player['player_name'],
                    'icon_torch' => 'leader'
                ));
            }
            if ($this->game->playerManager->hasLightsOutCard($id)) {
                $this->game->notifyAllPlayers('lightsOutCardPassed', clienttranslate( '${player_name} gains the ${icon_torch} card'), array(
                    'player_id' => $id,
                    'player_name' => $player['player_name'],
                    'icon_torch' => 'lights-out'
                ));
            }
        }
    }

    public function notifyCollectorUsed($playerId, $collector, $flippedTreasureCards) {
        $playerPerformingAction = $this->game->getPlayer($playerId);

        $this->game->notifyAllPlayers('collectorUsed', clienttranslate( '${player_name} activates ${icon_treasure} ${collector.name_translated}'), array(
            'player_id' => $playerId,
            'player_name' => $playerPerformingAction['player_name'],
            'player_score' => $this->game->scoreManager->getTotalScore($playerId),
            'collector' => $collector,
            'flippedTreasureCards' => $flippedTreasureCards,
            'icon_treasure' => $collector['treasure_type']
        ));
    }

    public function servantDieReRolled($playerId, $servantDie, $originalServantDie, $exhausted) {
        $player = $this->game->getPlayer($playerId);

        $additionalText = clienttranslate( 'which is equal or higher than ${effort}, so it is recovered');
        if ($exhausted) {
            $additionalText = clienttranslate( 'which is lower than ${effort}, so it remains exhausted');
        }
        $this->game->notifyAllPlayers('servantDieReRolled', clienttranslate( '${player_name} re-rolls ${icon_dice_1} into a ${icon_dice_2} ') .$additionalText, array(
            'playerId' => $playerId,
            'player_name' => $player['player_name'],
            'servantDie' => $servantDie,
            'effort' => $servantDie['effort'],
            'icon_dice_1' => [$originalServantDie],
            'icon_dice_2' => [$servantDie],
            'exhausted' => $exhausted
        ));
    }

    public function notifyTieBreakerRolled($playerId, $rolledServantDice) {
        $player = $this->game->getPlayer($playerId);
        $this->game->notifyAllPlayers('tieBreakerRolled', clienttranslate( '${player_name} is tied and rolls ${icon_dice} to break tie'), array(
            'playerId' => $playerId,
            'player_name' => $player['player_name'],
            'rolledServantDice' => $rolledServantDice,
            'icon_dice' => $rolledServantDice
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
