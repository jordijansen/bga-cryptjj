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
            'crypt.ActionManager',
            null, {
                game: null,

                constructor(game) {
                    this.game = game;
                },

                setup() {
                    console.log('ActionManager#setup')
                },

                claimTreasure(claimTreasureSelection) {
                    const args = {actionArgs: JSON.stringify(claimTreasureSelection)}
                    this._performAction('claimTreasure', args)
                },

                recoverServants() {
                    this._performAction('recoverServants')
                },

                activateCollector(collectorId, treasureCardIds = []) {
                    const args = {actionArgs: JSON.stringify({collectorId, treasureCardIds})}
                    this._performActionWithoutCheckAction('activateCollector', args)
                },

                _performAction(action, args, handler) {
                    if (!args) {
                        args = {};
                    }
                    args.lock = true;

                    if (this.game.checkAction(action)) {
                        this.game.ajaxcall("/" + this.game.game_name + "/" + this.game.game_name + "/" + action + ".html", args, this, (result) => console.log(`Succesfully performed ${action}`), handler);
                    }
                },

                _performActionWithoutCheckAction(action, args, handler) {
                    if (!args) {
                        args = {};
                    }
                    args.lock = true;

                    this.game.ajaxcall("/" + this.game.game_name + "/" + this.game.game_name + "/" + action + ".html", args, this, (result) => console.log(`Succesfully performed ${action}`), handler);
                },
            });
    }
);
