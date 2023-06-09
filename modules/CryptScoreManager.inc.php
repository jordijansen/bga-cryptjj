<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Crypt implementation : © Jordi Jansen <thestartplayer@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */
class CryptScoreManager extends APP_DbObject
{
    protected $game;

    public function __construct($game)
    {
        $this->game = $game;
    }

    /**
     * Each player starts the game with 3 un-exhausted servant dice, so they should start the game with a score of 3
     */
    public function setInitialScore($players) {
        foreach( $players as $playerId => $player )
        {
            $this->updateTotalScore($playerId);
        }
    }

    public function updateTotalScore($playerId, $isEndGame = false) {
        $totalScore = $this->calculateTotalScore($playerId, $isEndGame);
        self::DbQuery("UPDATE player SET player_score=".$totalScore." WHERE player_id = " .$playerId);
        return $this->getTotalScore($playerId);
    }

    public function getTotalScore($playerId) {
        return self::getUniqueValueFromDB("SELECT player_score FROM player WHERE player_id = " .$playerId);
    }

    public function getScoreBreakDownForPlayers($players) {
        $finalScoring = [];
        foreach($players as $playerId => $player)
        {
            $finalScoring[$playerId] = $this->getScoreBreakDown($playerId);
        }
        return $finalScoring;
    }

    private function getScoreBreakDown($playerId) {
        return [
            'treasureCardCoins' => $this->calculateTreasureCardCoins($playerId, true),
            'unExhaustedServantDice' => $this->calculateUnExhaustedServantDice($playerId),
            'collectors' => $this->calculateCollectors($playerId),
            'tieBreakerRoll' => self::getUniqueValueFromDB("SELECT player_score_aux FROM player WHERE player_id = " .$playerId),
            'totalScore' => self::getUniqueValueFromDB("SELECT player_score FROM player WHERE player_id = " .$playerId),
        ];
    }

    public function breakTies() {
        $tiedPlayerIds = $this->getTies();
        self::trace(json_encode($tiedPlayerIds));
        while (sizeof($tiedPlayerIds) > 0) {
            foreach($tiedPlayerIds as $tiedPlayerId)
            {
                $rolledValues = [];
                $servantDice = $this->game->servantDiceManager->getServantDiceInPlayerArea($tiedPlayerId['player_id']);
                foreach ($servantDice as $servanDie) {
                    $rolledValue = bga_rand(1, 6);
                    $this->game->servantDiceManager->setDieValue($servanDie['id'], $rolledValue);
                    $rolledValues[] = $rolledValue;
                }
                $auxScore = array_sum($rolledValues);
                self::DbQuery("UPDATE player SET player_score_aux=".$auxScore." WHERE player_id = " .$tiedPlayerId['player_id']);
                if (sizeof($rolledValues) > 0) {
                    $this->game->notificationsManager->notifyTieBreakerRolled($tiedPlayerId['player_id'], array_values($this->game->servantDiceManager->getServantDiceInPlayerArea($tiedPlayerId['player_id'])));
                }
            }
            $tiedPlayerIds = $this->getTies(true);
        }
    }

    private function getTies($retry = false) {
        if ($retry) {
            // If this is a retry and their player_score_aux is still zero this player has no dice
            return self::getObjectListFromDB('SELECT player_id 
                                                    FROM player p 
                                                    WHERE p.player_score IN (SELECT player_score FROM player GROUP BY player_score, player_score_aux HAVING COUNT(player_id) > 1)
                                                    AND p.player_score_aux IN (SELECT player_score_aux FROM player GROUP BY player_score, player_score_aux HAVING COUNT(player_id) > 1)
                                                    AND p.player_score_aux > 0');
        } else {
            return self::getObjectListFromDB('SELECT player_id 
                                                    FROM player p 
                                                    WHERE p.player_score IN (SELECT player_score FROM player GROUP BY player_score, player_score_aux HAVING COUNT(player_id) > 1)
                                                    AND p.player_score_aux IN (SELECT player_score_aux FROM player GROUP BY player_score, player_score_aux HAVING COUNT(player_id) > 1)');
        }
    }

    private function calculateTotalScore($playerId, $isEndGame) {
        $result = 0;
        $result = $result + $this->calculateTreasureCardCoins($playerId, $isEndGame);
        $result = $result + $this->calculateUnExhaustedServantDice($playerId);
        if ($isEndGame) {
            $result = $result + array_sum(array_column($this->calculateCollectors($playerId), 'score'));
        }
        return $result;
    }

    /**
     * Treasure cards are worth their coin value in points
     */
    private function calculateTreasureCardCoins($playerId, $includeHiddenInfo) {
        $treasureCards = $this->game->treasureCardsManager->findByPlayerId($playerId);
        if (!$includeHiddenInfo) {
            $treasureCards = array_filter($treasureCards, function($treasureCard) {
                return $treasureCard['face_up'] == '1';
            }, ARRAY_FILTER_USE_BOTH);
        }

        return array_sum(array_column($treasureCards, 'value'));
    }

    /**
     * Each remaining servant dice is worth 1 point
     */
    private function calculateUnExhaustedServantDice($playerId) {
        $servantDice = $this->game->servantDiceManager->getServantDiceInPlayerArea($playerId);
        return sizeof($servantDice);
    }

    private function calculateCollectors($playerId) {
        $result = [];
        $collectors = $this->game->collectorCardsManager->getCollectors();
        foreach($collectors as $collector) {
            if ($collector['ability_type'] == COLLECTOR_END_GAME) {
                $result[$collector['treasure_type']] = $collector;
                $result[$collector['treasure_type']]['nrOfCards'] = sizeof($this->game->treasureCardsManager->findByPlayerIdAndType($playerId, $collector['treasure_type']));
                $result[$collector['treasure_type']]['score'] = 0;
                if ($result[$collector['treasure_type']]['nrOfCards'] > 0) {
                    if ($collector['id'] == 'idol-B') {
                        $result[$collector['treasure_type']]['score'] = $this->calculateIdolB($playerId);
                    } else if ($collector['id'] == 'jewelery-A') {
                        $result[$collector['treasure_type']]['score'] = $this->calculateJeweleryA($playerId);
                    } else if ($collector['id'] == 'jewelery-B') {
                        $result[$collector['treasure_type']]['score'] = $this->calculateJeweleryB($playerId);
                    } else if ($collector['id'] == 'manuscript-A') {
                        $result[$collector['treasure_type']]['score'] = $this->calculateManuscriptA($playerId);
                    } else if ($collector['id'] == 'tapestry-A') {
                        $result[$collector['treasure_type']]['score'] = $this->calculateTapestryA($playerId);
                    } else if ($collector['id'] == 'tapestry-B') {
                        $result[$collector['treasure_type']]['score'] = $this->calculateTapestryB($playerId);
                    } else if ($collector['id'] == 'remains-B') {
                        $result[$collector['treasure_type']]['score'] = $this->calculateRemainsB($playerId);
                    } else if ($collector['id'] == 'pottery-A') {
                        $result[$collector['treasure_type']]['score'] = $this->calculatePotteryA($playerId);
                    }
                }
            }
        }
        return $result;
    }

    private function calculateIdolB($playerId) {
        $treasureCards = $this->game->treasureCardsManager->findByPlayerIdAndType($playerId, 'idol');
        $flippedTreasureCards = array_filter($treasureCards, function($treasureCard) {
            return $treasureCard['flipped'] == '1';
        }, ARRAY_FILTER_USE_BOTH);
        if (sizeof($flippedTreasureCards) >= 2) {
            return 5;
        } else if (sizeof($treasureCards) >= 2) {
            return 2;
        }
        return 0;
    }

    private function calculateJeweleryA($playerId) {
        $treasureCards = $this->game->treasureCardsManager->findByPlayerIdAndType($playerId, 'jewelery');
        if (sizeof($treasureCards) >= 2) {
            return max(array_column($treasureCards, 'value'));
        }
        return 0;
    }

    private function calculateJeweleryB($playerId) {
        $treasureCards = $this->game->treasureCardsManager->findByPlayerIdAndType($playerId, 'jewelery');
        return sizeof($treasureCards);
    }

    private function calculateManuscriptA($playerId) {
        $treasureCards = $this->game->treasureCardsManager->findByPlayerIdAndType($playerId, 'manuscript');
        $result = 0;
        if (sizeof($treasureCards) >= 2) {
            foreach ($treasureCards as $treasureCard) {
                $result = $result + (4 - $treasureCard['value']);
            }
        }
        return $result;
    }

    private function calculateTapestryA($playerId) {
        $players = $this->game->loadPlayersBasicInfos();

        $highestCoinValue = 0;
        $playerCoinValue = 0;
        foreach( $players as $id => $player )
        {
            $treasureCards = $this->game->treasureCardsManager->findByPlayerIdAndType($id, 'tapestry');
            if (sizeof($treasureCards) > 0) {
                $coinValue = array_sum(array_column($treasureCards, 'value'));
                if ($coinValue > $highestCoinValue) {
                    $highestCoinValue = $coinValue;
                }
                if ($playerId == $id) {
                    $playerCoinValue = $coinValue;
                }
            }
        }

        if ($playerCoinValue > 0 && $playerCoinValue == $highestCoinValue) {
            return 5;
        }
        return 0;
    }

    private function calculateTapestryB($playerId) {
        $players = $this->game->loadPlayersBasicInfos();
        $playersWith3OrMoreTapestryCards = [];
        foreach( $players as $id => $player )
        {
            $treasureCards = $this->game->treasureCardsManager->findByPlayerIdAndType($id, 'tapestry');
            if (sizeof($treasureCards) >= 3) {
                $playersWith3OrMoreTapestryCards[] = $id;
            }
        }
        if (sizeof($playersWith3OrMoreTapestryCards) >= 1) {
            if (in_array($playerId, $playersWith3OrMoreTapestryCards)) {
                return sizeof($playersWith3OrMoreTapestryCards) == 1 ? 7 : 4;
            }
        }
        return 0;
    }

    private function calculateRemainsB($playerId) {
        $treasureCards = $this->game->treasureCardsManager->findByPlayerIdAndType($playerId, 'remains');
        if (sizeof($treasureCards) >= 4) {
            return 10;
        }
        return 0;
    }

    private function calculatePotteryA($playerId) {
        $treasureCards = $this->game->treasureCardsManager->findByPlayerIdAndType($playerId, 'pottery');
        if (sizeof($treasureCards) == 2) {
            return 2;
        } else if (sizeof($treasureCards) == 3) {
            return 4;
        } else if (sizeof($treasureCards) >= 4) {
            return 8;
        }
        return 0;
    }
}
