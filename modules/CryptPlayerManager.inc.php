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
 * CryptTorchCards.inc.php
 */
class CryptPlayerManager extends APP_DbObject
{
    protected $game;

    public function __construct($game)
    {
        $this->game = $game;
    }

    public function distributeInitialTorchCards($players)
    {
        self::DbQuery("UPDATE player SET custom_order = player_no");
    }

    public function hasLeaderCard($playerId) {
        return self::getUniqueValueFromDB("SELECT custom_order FROM player WHERE player_id = " .$playerId) == 1;
    }

    public function hasLightsOutCard($playerId) {
        $playerCount = $this->game->getPlayerCount();
        $orderNumberWhoHasLightsOutCard = $playerCount == 2 ? 1 : $playerCount;
        return self::getUniqueValueFromDB("SELECT custom_order FROM player WHERE player_id = " .$playerId) == $orderNumberWhoHasLightsOutCard;
    }

    public function hasBothCards($playerId) {
        return $this->hasLeaderCard($playerId) && $this->hasLightsOutCard($playerId);
    }

    public function getLeaderPlayerId() {
        return self::getUniqueValueFromDB("SELECT player_id FROM player WHERE custom_order = 1");
    }

    public function getPlayersInTurnOrder() {
        return self::getCollectionFromDb("SELECT player_id id, custom_order, has_used_manuscript_b_this_round, has_played_before_this_round FROM player ORDER BY custom_order DESC");
    }

    public function passTorchCards($players) {
        foreach( $players as $playerId => $player )
        {
            $currentOrderNo = self::getUniqueValueFromDB("SELECT custom_order FROM player WHERE player_id = " .$playerId);
            $newPlayerOrderNo = $currentOrderNo == sizeof($players) ? 1 : $currentOrderNo + 1;

            self::DbQuery("UPDATE player SET has_played_before_this_round=0, has_used_manuscript_b_this_round=0, custom_order=".$newPlayerOrderNo." WHERE player_id = " .$playerId);
        }
    }
}
