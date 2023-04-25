/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * CryptJj implementation : © Jordi Jansen <thestartplayer@gmail.com>
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
            'crypt.DeckManager',
            null, {
                game: null,

                constructor(game) {
                    this.game = game;
                },

                setup() {
                    console.log("DeckManager#setup")
                    // Set-up treasure deck
                    this.update(this.game.gamedatas.treasureDeck)
                },

                update(treasureDeck) {
                    dojo.place(this.game.format_block('jstpl_treasure_deck', treasureDeck), "treasure-cards-deck", "only")
                }
            });
    }
);
