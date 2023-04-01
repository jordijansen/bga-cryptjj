/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Villagersnew implementation : © Sandra Kuipers sandra@skuipers.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */

define(
    [
      'dojo',
      'dojo/_base/declare',
      'ebg/counter',
    ],
    (dojo, declare) => {
      return declare(
          'crypt.PlayerManager',
          null, {
            game: null,

            constructor(game) {
              this.game = game;
            },

            setup() {
                console.log("PlayerManager#setup")
                // Set-up player areas
                let thisPlayerArea;
                const otherPlayerAreas = [];
                for( var player_id in this.game.gamedatas.players )
                {
                    var player = this.game.gamedatas.players[player_id];
                    var playerArea = this.game.format_block('jstpl_player_area', {
                        "id": player.id,
                        "color" : player.color,
                        "name": player.name
                    });
                    if (Number(player.id) === this.game.player_id) {
                        thisPlayerArea = playerArea;
                    } else {
                        otherPlayerAreas.push(playerArea);
                    }
                }
                dojo.place(thisPlayerArea, "player-areas-row")
                otherPlayerAreas.forEach(playerArea => dojo.place(playerArea, "player-areas-row"))
            },
          });
    }
);
