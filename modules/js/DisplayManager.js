/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Villagersnew implementation : © Sandra Kuipers sandra@skuipers.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */

const blankCurrentSelection = { servantDice: [], value: 1, valid: true };

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
                cardDisplay: null,
                availableServants: [],
                availableServantsIndex: -1,
                claimTreasureMode: false,

                constructor(game) {
                    this.game = game;
                },

                setup() {
                    console.log("DisplayManager#setup")

                    // Set-up treasure display
                    this.cardDisplay = new ebg.zone();
                    this.cardDisplay.create(this.game, $('treasure-cards-display'), this.game.cardWidth, this.game.cardHeight);
                    this.cardDisplay.item_margin = 20;

                    const { cards } = this.game.gamedatas.treasureDisplay;
                    this.createCardAndAddToDisplay(cards);
                },

                createCardAndAddToDisplay(cards) {
                    for (const card of cards) {
                        const treasureCard = this.game.format_block('jstpl_treasure_card', card);
                        dojo.place(treasureCard, 'treasure-cards-display')
                        this.cardDisplay.placeInZone(`treasure-card-${card.id}`)
                        dojo.connect($(`increase-dice-${card.id}`), 'onclick', this, 'onIncreaseDiceClicked')
                        dojo.connect($(`dice-selection-area-${card.id}`), 'onclick', this, 'onAddDiceClicked')
                        dojo.connect($(`decrease-dice-${card.id}`), 'onclick', this, 'onDecreaseDiceClicked')
                    }
                },

                enterClaimTreasureMode() {
                    this.claimTreasureMode = true;
                    this.availableServants = this.game.servantManager.getServantDieInPlayerArea(this.game.player_id).map(die => die.id);
                    this.availableServantsIndex = -1;

                    this.toggleSelectableCards(true);
                },

                exitClaimTreasureMode(resetServants) {
                    this.claimTreasureMode = false;
                    this.cardDisplay.getAllItems()
                        .forEach(id => {
                            dojo.removeClass($(id), 'selectable')
                            dojo.removeClass($(id), 'invalid')
                            dojo.removeClass($(id), 'selected')

                            if (resetServants) {
                                this.game.servantManager.getServantDieForTreasureCardSelection(id.replace('treasure-card-', ''))
                                    .forEach(die => this.game.servantManager.moveServantDieToPlayerArea(die.id, this.game.player_id))
                            }
                        });
                },

                toggleSelectableCards(show) {
                    console.log("DisplayManager#showSelectableCards")
                    this.cardDisplay.getAllItems().forEach(id => {
                        if (show) {
                            dojo.addClass($(id), 'selectable')
                        } else {
                            dojo.removeClass($(id), 'selectable')
                        }
                    })
                },

                addServantDieToCard(servantId, targetCardId) {
                    console.log("DisplayManager#addOrRemoveServantDieFromSelection")
                    console.log(servantId + '-' + targetCardId)

                    // Retrieve the servant dice already there
                    const servantDieAlreadyInSelection = this.game.servantManager.getServantDieForTreasureCardSelection(targetCardId);

                    let valueToUse = 1;

                    if (servantDieAlreadyInSelection.length > 0) {
                        valueToUse = servantDieAlreadyInSelection[0].location_arg;
                    } else if (servantDieAlreadyInSelection.length === 0) {
                        if (this.game.playerManager.hasLightsOutCard(this.game.player_id)) {
                            this.toggleSelectableCards(false);
                        }
                    }

                    this.game.servantManager.moveServantDieToTreasureCardSelectionArea(servantId, targetCardId, valueToUse);

                    this.updateInvalid();
                    this.updateSelected()
                },

                updateInvalid() {
                    this.cardDisplay.getAllItems()
                        .forEach(id => {
                            if (this.isValidSelection(id.replace('treasure-card-', ''))) {
                                dojo.removeClass($(id), 'invalid')
                            } else {
                                dojo.addClass($(id), 'invalid')
                            }
                        })
                },

                updateSelected() {
                    this.cardDisplay.getAllItems()
                        .forEach(id => {
                            if (this.game.servantManager.getServantDieForTreasureCardSelection(id.replace('treasure-card-', '')).length > 0) {
                                dojo.addClass($(id), 'selected')
                            } else {
                                dojo.removeClass($(id), 'selected')
                            }
                        })
                },

                isValidSelection(cardId) {
                    const diceOnCard = this.game.servantManager.getServantDieForTreasureCard(cardId);
                    const diceSelectionOnCard = this.game.servantManager.getServantDieForTreasureCardSelection(cardId);
                    console.log(diceOnCard);
                    // If no dice already on card, or no dice selection assigned to it always true
                    if (diceOnCard.length === 0 || diceSelectionOnCard.length === 0) {
                        return true;
                    } else {
                        const totalEffortOnCard = diceOnCard.reduce((sum, a) => sum + Number(a['location_arg']), 0);
                        const selectedEffortOnCard = diceSelectionOnCard.length * diceSelectionOnCard[0].location_arg;
                        console.log(totalEffortOnCard + ' - ' + selectedEffortOnCard)
                        return selectedEffortOnCard > totalEffortOnCard;
                    }
                },

                isCurrentSelectionValid() {
                    return this.cardDisplay.getAllItems()
                        .map(id => id.replace('treasure-card-', ''))
                        .every(id => this.isValidSelection(id));
                },

                getCurrentSelection() {
                    return this.cardDisplay.getAllItems()
                        .map(id => id.replace('treasure-card-', ''))
                        .filter(id => this.game.servantManager.getServantDieForTreasureCardSelection(id).length > 0)
                        .map(id => (
                            {
                                id: id,
                                value: this.game.servantManager.getServantDieForTreasureCardSelection(id)[0].location_arg,
                                servantDice: this.game.servantManager.getServantDieForTreasureCardSelection(id)
                                    .map(die => die.id)
                            }
                        ))
                },

                // Click Handlers
                onDecreaseDiceClicked(event) {
                    if (this.claimTreasureMode) {
                        console.log("DisplayManager#onDecreaseDiceClicked")
                        const cardId = event.currentTarget.dataset.id;
                        event.preventDefault();
                        event.stopPropagation();

                        const servantDieAlreadyInSelection = this.game.servantManager.getServantDieForTreasureCardSelection(cardId);
                        if (servantDieAlreadyInSelection.length > 0 && servantDieAlreadyInSelection[0].location_arg > 1) {
                            const newValue = servantDieAlreadyInSelection[0].location_arg - 1;
                            servantDieAlreadyInSelection.forEach(die => this.game.servantManager.setServantDieValue(die.id, newValue))
                            this.updateInvalid();
                        }
                    }
                },

                onIncreaseDiceClicked(event) {
                    if (this.claimTreasureMode) {
                        console.log("DisplayManager#onIncreaseDiceClicked")
                        const cardId = event.currentTarget.dataset.id;
                        event.preventDefault();
                        event.stopPropagation();

                        const servantDieAlreadyInSelection = this.game.servantManager.getServantDieForTreasureCardSelection(cardId);
                        console.log(servantDieAlreadyInSelection);
                        if (servantDieAlreadyInSelection.length > 0 && servantDieAlreadyInSelection[0].location_arg < 6) {
                            const newValue = servantDieAlreadyInSelection[0].location_arg + 1;
                            servantDieAlreadyInSelection.forEach(die => this.game.servantManager.setServantDieValue(die.id, newValue))
                            this.updateInvalid();
                        }
                    }
                },

                onAddDiceClicked(event) {
                    if (this.claimTreasureMode) {
                        console.log("DisplayManager#onAddDiceClicked")
                        const cardId = event.currentTarget.dataset.id;
                        event.preventDefault();
                        event.stopPropagation();

                        const diceStillInPlayerArea = this.game.servantManager.getServantDieInPlayerArea(this.game.player_id);
                        if (diceStillInPlayerArea.length > 0) {
                            this.addServantDieToCard(diceStillInPlayerArea[0].id, cardId);
                        } else {
                            const servantDieAlreadyInSelection = this.game.servantManager.getServantDieForTreasureCardSelection(cardId);
                            const diceForPlacement = this.availableServants.filter(id => !servantDieAlreadyInSelection.some(die => die.id === id))
                            if (diceForPlacement.length > 0) {
                                this.availableServantsIndex = (this.availableServantsIndex + 1) % diceForPlacement.length

                                this.addServantDieToCard(diceForPlacement[this.availableServantsIndex], cardId);
                            } else {
                                this.game.showMessage( _('No available servants left'), 'error');
                            }
                        }
                    }
                },
            });
    }
);
