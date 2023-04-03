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
            'crypt.ServantManager',
            null, {
                game: null,

                constructor(game) {
                    this.game = game;
                },

                setup() {
                    console.log("ServantManager#setup")
                    // Set-up servant dice
                    for (const playerId in this.game.gamedatas.servantDice) {
                        const servantDiceForPlayer = this.game.gamedatas.servantDice[playerId];
                        for (const dieId in servantDiceForPlayer) {
                            const servantDie = servantDiceForPlayer[dieId];
                            const servantDieElement = this.game.format_block('jstpl_die', {
                                "id": servantDie.id,
                                "type": servantDie.type,
                                "color": servantDie.type_arg
                            });
                            if (servantDie.location === 'player_area') {
                                dojo.place(servantDieElement, dojo.query(`#player-area-${servantDie.type} .dice-placement-area`)[0])
                            } else if (servantDie.location.startsWith('treasure_card_')) {
                                const treasureCardId = servantDie.location.replace('treasure_card_', '');
                                dojo.place(servantDieElement, dojo.query(`#treasure-card-${treasureCardId} .dice-placement-area`)[0])
                            } else if (servantDie.location === 'exhausted') {
                                // TODO implement placement on exhausted
                            }
                            // Set the value of the die
                            this.setServantDieValue(servantDie.id, servantDie.location_arg)
                            // Add the onclick listener
                            dojo.connect($('servant-die-' + servantDie.id), 'onclick', this, "onServantDieClicked")
                        }
                    }
                },

                showSelectableServants() {
                    console.log("ServantManager#showSelectableServants")
                    var servantDiceForPlayer = this.game.gamedatas.servantDice[this.game.player_id]
                    for (const dieId in servantDiceForPlayer) {
                        // TODO check if die is actually selectable based on previously deployed servants and number of dice left
                        dojo.addClass($('servant-die-' + dieId), 'selectable')
                    }
                },

                hideSelectableServants() {
                    console.log("ServantManager#hideSelectableServants")
                    var servantDiceForPlayer = this.game.gamedatas.servantDice[this.game.player_id]
                    for (const dieId in servantDiceForPlayer) {
                        dojo.removeClass($('servant-die-' + dieId), 'selectable')
                    }
                },

                onServantDieClicked(event) {
                    console.log("ServantManager#onServantDieClicked")
                    event.stopPropagation();

                    const clickedServantDie = this.game.gamedatas.servantDice[this.game.player_id][event.currentTarget.dataset.id]
                    if (!clickedServantDie) {
                        console.log("Clicked die unknown to this player, ignoring")
                        return;
                    }

                    if (this.game.gamedatas.gamestate.name === this.game.gameStates.claimTreasureStep2) {
                        this.game.displayManager.addOrRemoveServantDieFromSelection(event.currentTarget.dataset.id)
                    }
                },

                setServantDieValue(dieId, value) {
                    const servantDie = $(`servant-die-${dieId}`);
                    dojo.empty(servantDie);
                    for (let i = 0; i < value; i++) {
                        dojo.create("span", { class: "pip" }, servantDie);
                    }
                },

                moveServantDieToPlayerArea(servantId, playerId) {
                    phantomMove($('servant-die-' + servantId), dojo.query(`#player-area-${playerId} .dice-placement-area`)[0], 500);
                    this.setServantDieValue(servantId, 1);
                },

                moveServantDieToTreasureCardSelectionArea(servantId, cardId, value) {
                    phantomMove($('servant-die-' + servantId), dojo.query(`#treasure-card-${cardId} .dice-selection-area`)[0], 500, false);
                    this.setServantDieValue(servantId, value);
                },

                moveServantDieToTreasureCard(servantId, cardId, value) {
                    phantomMove($('servant-die-' + servantId), dojo.query(`#treasure-card-${cardId} .dice-placement-area`)[0], 500);
                    this.setServantDieValue(servantId, value);
                }
    });
    }
);
