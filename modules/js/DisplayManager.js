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
          'crypt.DisplayManager',
          null, {
            game: null,

            constructor(game) {
              this.game = game;
            },

            setup(gameData = {
                treasureDisplay: {
                    cards: [{
                        id: 1,
                        type: 'idol',
                        value: 4
                    }]
                }
            }) {
                console.log("DisplayManager#setup")
                // Set-up treasure display
                for (const card of gameData.treasureDisplay.cards) {
                    const treasureCard = this.game.format_block('jstpl_treasure_card', card);
                    dojo.place(treasureCard, "treasure-cards-display")
                }
            },
          });
    }
);
