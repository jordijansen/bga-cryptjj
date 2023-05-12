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
 * CryptTorchCards.inc.php
 */
class CryptTorchCards extends APP_DbObject
{
    protected $game;

    public function __construct($game)
    {
        $this->game = $game;
    }

    /**
     * Distribute the Torch and Last Light cards to the players
     * For a 2 player game player_no 1 gains both cards
     * For a 3 and 4 player game player_no 1 gains the torch card and the last player the last light card
     */
    public function distributeInitialTorchCards($players)
    {
        $leaderPlayerNo = 1;
        $lightsOutPlayerNo = sizeof($players);
        if (sizeof($players) === 2) {
            $lightsOutPlayerNo = 1;
        }

        self::DbQuery("UPDATE player SET has_torch_card_leader=1 WHERE player_no = ".$leaderPlayerNo);
        self::DbQuery("UPDATE player SET has_torch_card_lights_out=1 WHERE player_no = ".$lightsOutPlayerNo);
    }

    public function hasLeaderCard($playerId) {
        return self::getUniqueValueFromDB("SELECT has_torch_card_leader FROM player WHERE player_id = " .$playerId) == 1;
    }

    public function hasLightsOutCard($playerId) {
        return self::getUniqueValueFromDB("SELECT has_torch_card_lights_out FROM player WHERE player_id = " .$playerId) == 1;
    }

    public function hasBothCards($playerId) {
        return $this->hasLeaderCard($playerId) && $this->hasLightsOutCard($playerId);
    }

    public function getLeaderPlayerId() {
        return self::getUniqueValueFromDB("SELECT player_id FROM player WHERE has_torch_card_leader = 1");
    }

    public function passTorchCards($players) {
        $currentLeaderPlayerNo = self::getUniqueValueFromDB("SELECT player_no FROM player WHERE has_torch_card_leader = 1");
        $currentLightsOutPlayerNo = self::getUniqueValueFromDB("SELECT player_no FROM player WHERE has_torch_card_lights_out = 1");

        $leaderPlayerNo = $currentLeaderPlayerNo == sizeof($players) ? 1 : $currentLeaderPlayerNo + 1;
        $lightsOutPlayerNo = $currentLightsOutPlayerNo == sizeof($players) ? 1 : $currentLightsOutPlayerNo + 1;

        self::DbQuery("UPDATE player SET has_played_before_this_round=0, has_torch_card_leader=0, has_torch_card_lights_out=0, has_used_manuscript_b_this_round=0");
        self::DbQuery("UPDATE player SET has_torch_card_leader=1 WHERE player_no = ".$leaderPlayerNo);
        self::DbQuery("UPDATE player SET has_torch_card_lights_out=1 WHERE player_no = ".$lightsOutPlayerNo);
    }

}
