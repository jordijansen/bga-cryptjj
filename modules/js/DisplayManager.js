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
                currentSelectedCard: null,
                currentSelection: {},

                constructor(game) {
                    this.game = game;
                },

                setup() {
                    console.log("DisplayManager#setup")
                    // Set-up treasure display
                    const { cards } = this.game.gamedatas.treasureDisplay;
                    for (const card of cards) {
                        const treasureCard = this.game.format_block('jstpl_treasure_card', card);

                        this.currentSelection[card.id] = { servantDice: [], value: 1 }

                        dojo.place(treasureCard, 'treasure-cards-display')
                        dojo.connect($(`treasure-card-${card.id}`), 'onclick', this, 'onDisplayCardClicked')
                        dojo.connect($(`increase-dice-${card.id}`), 'onclick', this, 'onIncreaseDiceClicked')
                        dojo.connect($(`decrease-dice-${card.id}`), 'onclick', this, 'onDecreaseDiceClicked')
                    }
                },

                enterClaimTreasureMode() {
                    this.toggleSelectableCards(true);
                },

                exitClaimTreasureMode() {
                    this.toggleSelectableCards(false);
                    this.selectCard(null);

                    for (const cardId in this.currentSelection) {
                        this.currentSelection[cardId] = { servantDice: [], value: 1 }
                    }
                },

                toggleSelectableCards(show) {
                    console.log("DisplayManager#showSelectableCards")
                    const { cards } = this.game.gamedatas.treasureDisplay;
                    for (const card of cards) {
                        if (show) {
                            // TODO check if card is actually selectable based on previously deployed servants and number of dice left
                            dojo.addClass($('treasure-card-' + card.id), 'selectable')
                        } else {
                            dojo.removeClass($('treasure-card-' + card.id), 'selectable')
                        }

                    }
                },

                selectCard(cardId) {
                    console.log("DisplayManager#selectCard")
                    if (this.currentSelectedCard != null && this.currentSelectedCard !== cardId) {
                        const oldSelectedCard = $('treasure-card-' + this.currentSelectedCard );
                        dojo.removeClass(oldSelectedCard, 'selected');
                    }

                    this.currentSelectedCard = cardId;
                    if (cardId) {
                        const newSelectedCard = $('treasure-card-' + cardId);
                        dojo.addClass(newSelectedCard, 'selected')
                    }
                },

                addOrRemoveServantDieFromSelection(servantId) {
                    console.log("DisplayManager#addOrRemoveServantDieFromSelection")

                    console.log(this.currentSelection)

                    if (this.currentSelection[this.currentSelectedCard].servantDice.includes(servantId)) {
                        // Remove the servant die from the currentSelectedCard and move it back to the player area
                        this.removeServantDieFromCard(this.currentSelectedCard, servantId);

                        // Move the die back to the player area and reset the die value to 1
                        this.game.servantManager.moveServantDieToPlayerArea(servantId, this.game.player_id)
                    } else {
                        // Remove the servant die from a treasure card if it had been placed there previously
                        for (var cardId in this.currentSelection) {
                            this.removeServantDieFromCard(cardId, servantId);
                        }
                        // Add the the servant die to the currentSelectedCard and move it there
                        this.currentSelection[this.currentSelectedCard].servantDice.push(servantId);

                        // Move the die to the treasure card and set the die value to thats card current value
                        this.game.servantManager.moveServantDieToTreasureCardSelectionArea(servantId, this.currentSelectedCard, this.currentSelection[this.currentSelectedCard].value)
                    }
                },

                removeServantDieFromCard(cardId, servantId) {
                    const indexOf = this.currentSelection[cardId].servantDice.indexOf(servantId);
                    if (indexOf > -1) {
                        this.currentSelection[cardId].servantDice.splice(indexOf, 1)
                        if (this.currentSelection[cardId].servantDice.length === 0) {
                            this.currentSelection[cardId].value = 1;
                        }
                    }
                },

                // Click Handlers
                onDisplayCardClicked(event) {
                    console.log("DisplayManager#onDisplayCardClicked")
                    if (this.game.gamedatas.gamestate.name === this.game.gameStates.claimTreasureStep1) {
                        this.selectCard(event.currentTarget.dataset.id)

                        this.game.setClientState(this.game.gameStates.claimTreasureStep2, {
                            descriptionmyturn: _("${you} must select servant dice to use for this treasure")
                        })
                        this.game.servantManager.showSelectableServants();
                    } else if (this.game.gamedatas.gamestate.name === this.game.gameStates.claimTreasureStep2) {
                        this.selectCard(event.currentTarget.dataset.id)
                    }
                },

                onIncreaseDiceClicked(event) {
                    console.log("DisplayManager#onIncreaseDiceClicked")
                    event.preventDefault();
                    event.stopPropagation();

                    if (this.currentSelection[this.currentSelectedCard].servantDice.length > 0 &&
                        this.currentSelection[this.currentSelectedCard].value < 6) {
                        this.currentSelection[this.currentSelectedCard].value++
                        this.currentSelection[this.currentSelectedCard].servantDice.forEach(dieId => this.game.servantManager.setServantDieValue(dieId, this.currentSelection[this.currentSelectedCard].value))
                        console.log(this.currentSelection[this.currentSelectedCard]);
                    }

                },

                onDecreaseDiceClicked(event) {
                    console.log("DisplayManager#onDecreaseDiceClicked")
                    event.preventDefault();
                    event.stopPropagation();

                    if (this.currentSelection[this.currentSelectedCard].servantDice.length > 0 &&
                        this.currentSelection[this.currentSelectedCard].value > 1) {
                        this.currentSelection[this.currentSelectedCard].value--
                        this.currentSelection[this.currentSelectedCard].servantDice.forEach(dieId => this.game.servantManager.setServantDieValue(dieId, this.currentSelection[this.currentSelectedCard].value))
                        console.log(this.currentSelection[this.currentSelectedCard]);
                    }
                },
            });
    }
);
