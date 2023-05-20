/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Crypt implementation : © Jordi Jansen <thestartplayer@gmail.com>
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
            'crypt.TreasureCardManager',
            null, {
                game: null,
                availableServants: [],
                availableServantsIndex: -1,
                claimTreasureMode: false,
                selectTreasureModePlayerArea: {},
                selectTreasureModeDisplay: {},
                cardDisplay: null,
                playerAreas: {},

                constructor(game) {
                    this.game = game;
                },

                setup() {
                    console.log("TreasureCardManager#setup")

                    // Set-up treasure display
                    this.cardDisplay = new ebg.zone();
                    this.cardDisplay.create(this.game, $('treasure-cards-display'), this.game.cardWidth, this.game.cardHeight);
                    this.cardDisplay.item_margin = 10;

                    this.cardDiscard = new ebg.zone();
                    this.cardDiscard.create(this.game, $('treasure-cards-discard'), this.game.cardWidth, this.game.cardHeight);
                    this.cardDiscard.setPattern('diagonal');
                    this.cardDiscard.item_margin = 0;

                    // Set-up players treasure area
                    Object.entries(this.game.gamedatas.players).forEach(([playerId, player]) => {
                        this.playerAreas[playerId] = {
                            tapestry: new ebg.zone(),
                            idol: new ebg.zone(),
                            remains: new ebg.zone(),
                            pottery: new ebg.zone(),
                            jewelery: new ebg.zone(),
                            manuscript: new ebg.zone(),
                        }
                        Object.entries(this.playerAreas[playerId]).forEach(([type, zone]) => {
                            zone.create(this.game, $(`player-${playerId}-treasure-${type}`), this.game.cardWidth, this.game.cardHeight);
                            zone.setPattern('verticalfit')
                        });
                    });

                    this.renderCardsAndMoveToZone(this.game.gamedatas.treasureCards);
                },

                renderCardsAndMoveToZone(cards, replace = false) {
                    for (const card of cards) {
                        const elementId = `treasure-card-${card.id}`;
                        if (!replace) {
                            const treasureCard = this.createTreasureCard(card);
                            dojo.place(treasureCard, 'treasure-cards-deck')
                        }
                        console.log(card);
                        const cardValue = card.location.startsWith('player_area_') && card.flipped === '0' ? 'back' : card.value;
                        dojo.attr(elementId, 'class', `crypt-card treasure-card treasure-card-${card.type} treasure-card-${card.type}-${cardValue} ${card.face_up === '0' ? 'face-down' : 'face-up'} ${card.flipped === '1' ? 'flipped' : 'un-flipped'}`)
                        if (card.location === 'display') {
                            if (!replace) {
                                this.setMaxWidthOfDisplay();
                            }
                            this.cardDisplay.placeInZone(`treasure-card-${card.id}`)
                            const increaseButton = $(`increase-dice-${card.id}`);
                            this.game.disconnect(increaseButton, 'onclick')
                            this.game.connect(increaseButton, 'onclick', (e) => this.onIncreaseDiceClicked(e))
                            const decreaseButton = $(`decrease-dice-${card.id}`);
                            this.game.disconnect(decreaseButton, 'onclick')
                            this.game.connect(decreaseButton, 'onclick', (e) => this.onDecreaseDiceClicked(e))
                        } else if (card.location === 'discard') {
                            this.cardDiscard.placeInZone(`treasure-card-${card.id}`)
                        } else if (card.location.startsWith('player_area_')) {
                            const playerId = card.location.replace('player_area_', '');
                            this.moveTreasureCardToPlayerArea(card, playerId);
                        }
                        if (card.location !== 'display') {
                            this.game.addTooltipHtml(`treasure-card-${card.id}`, this.renderTooltip(card), 800);
                        }

                        this.renderDiscardPileTooltip();

                        this.game.disconnect($(elementId), 'onclick')
                        this.game.connect($(elementId), 'onclick', (e) => this.onTreasureCardClicked(e))
                    }
                },

                setMaxWidthOfDisplay() {
                    const cardsAlreadyInDisplay = dojo.query('#treasure-cards-display .treasure-card')
                    dojo.style('treasure-cards-display', 'max-width', (cardsAlreadyInDisplay.length + 1) * 189 + 'px');
                },

                enterClaimTreasureMode() {
                    this.claimTreasureMode = true;
                    this.availableServants = this.game.servantManager.getServantDieInPlayerArea(this.game.player_id).map(die => die.id);
                    this.availableServantsIndex = -1;

                    this.toggleClaimableDisplayCards(true);
                },

                exitClaimTreasureMode(resetServants) {
                    this.claimTreasureMode = false;
                    this.toggleClaimableDisplayCards(false)
                    this.cardDisplay.getAllItems().forEach(id => {
                            if (resetServants) {
                                this.game.servantManager.getServantDieForTreasureCardSelection(id.replace('treasure-card-', ''))
                                    .forEach(die => this.game.servantManager.moveServantDieToPlayerArea(die.id, this.game.player_id))
                            }
                        });
                },

                toggleClaimableDisplayCards(show) {
                    console.log("TreasureCardManager#showSelectableCards")
                    this.cardDisplay.getAllItems().forEach(id => {
                        if (show) {
                            dojo.addClass($(id), 'selectable')
                            dojo.addClass($(id), 'selection')
                        } else {
                            dojo.removeClass($(id), 'selection')
                            dojo.removeClass($(id), 'selectable')
                            dojo.removeClass($(id), 'invalid')
                            dojo.removeClass($(id), 'selected')
                            dojo.removeClass($(id), 'has-dice-selection')
                        }
                    })
                },

                enterSelectTreasureModePlayerArea(treasureType, nrOfCardsToSelect) {
                    this.selectTreasureModePlayerArea = { active: true, nrOfCardsToSelect, treasureType };
                    this.toggleSelectablePlayerAreaCards(treasureType, true);
                    const selectableCards = dojo.query('.player-treasure-area .treasure-card.selectable');
                    // Auto select cards if the number to select matches the available cards
                    if (selectableCards.length === Number(nrOfCardsToSelect)) {
                        selectableCards.forEach(card => {
                            dojo.addClass($(card.id), 'selected');
                        })
                    }
                },

                enterSelectTreasureModeDisplay(nrOfCardsToSelect) {
                    this.selectTreasureModeDisplay = { active: true, nrOfCardsToSelect };
                    this.toggleSelectableDisplayCards(true);
                    const selectableCards = dojo.query('#treasure-cards-display .treasure-card.selectable');
                    // Auto select cards if the number to select matches the available cards
                    if (selectableCards.length === Number(nrOfCardsToSelect)) {
                        selectableCards.forEach(card => {
                            dojo.addClass($(card.id), 'selected');
                        })
                    }
                },

                exitSelectTreasureMode() {
                    if (this.selectTreasureModePlayerArea.active) {
                        this.toggleSelectablePlayerAreaCards(this.selectTreasureModePlayerArea.treasureType, false);
                        this.toggleSelectableDisplayCards(false);

                        this.selectTreasureModePlayerArea = { active: false };
                        this.selectTreasureModeDisplay = { active: false };
                    }
                },

                toggleSelectablePlayerAreaCards(type, show) {
                    console.log("TreasureCardManager#toggleSelectablePlayerAreaCards#" + type)
                    this.getTreasureCardsInPlayerAreaOfType(this.game.player_id, type).forEach(id => {
                        if (show && this.isTreasureCardUnFlipped(id)) {
                            dojo.addClass($(id), 'selectable')
                        } else {
                            dojo.removeClass($(id), 'selectable')
                            dojo.removeClass($(id), 'invalid')
                            dojo.removeClass($(id), 'selected')
                        }
                    });
                },

                toggleSelectableDisplayCards(show) {
                    console.log("TreasureCardManager#toggleSelectableDisplayCards")
                    this.cardDisplay.getAllItems()
                        .filter(id => dojo.hasClass($(id), 'face-down'))
                        .forEach(id => {
                        if (show) {
                            dojo.addClass($(id), 'selectable')
                        } else {
                            dojo.removeClass($(id), 'selectable')
                            dojo.removeClass($(id), 'invalid')
                            dojo.removeClass($(id), 'selected')
                        }
                    });
                },

                getTreasureCardsInPlayerAreaOfType(playerId, type) {
                    return this.playerAreas[playerId][type].getAllItems();
                },

                getTreasureCardsInDisplay() {
                    return this.cardDisplay.getAllItems();
                },

                isTreasureCardFaceDown(id) {
                    return dojo.hasClass($(id), 'face-down');
                },

                isTreasureCardUnFlipped(id) {
                    return dojo.hasClass($(id), 'un-flipped');
                },

                addServantDieToCard(servantId, targetCardId) {
                    console.log("TreasureCardManager#addOrRemoveServantDieFromSelection")
                    console.log(servantId + '-' + targetCardId)

                    // Retrieve the servant dice already there
                    const servantDieAlreadyInSelection = this.game.servantManager.getServantDieForTreasureCardSelection(targetCardId);

                    let valueToUse = 1;

                    if (servantDieAlreadyInSelection.length > 0) {
                        valueToUse = servantDieAlreadyInSelection[0].location_arg;
                    } else if (servantDieAlreadyInSelection.length === 0) {
                        if (this.game.playerManager.getPlayerCount() === 2
                            && this.game.playerManager.hasLightsOutCard(this.game.player_id)
                            && this.game.playerManager.hasLeaderCard(this.game.player_id)
                            && this.game.playerManager.hasPlayedBeforeThisRound()) {
                            this.toggleClaimableDisplayCards(false);
                        } else if (this.game.playerManager.getPlayerCount() > 2
                            && this.game.playerManager.hasLightsOutCard(this.game.player_id)) {
                            this.toggleClaimableDisplayCards(false);
                        }
                    }

                    this.game.servantManager.moveServantDieToTreasureCardSelectionArea(servantId, targetCardId, valueToUse);

                    this.updateHasDiceSelection();
                    this.updateInvalid();
                    this.updateSelected();
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

                updateHasDiceSelection(forceRemove = false) {
                    this.cardDisplay.getAllItems()
                        .forEach(id => {
                            if (!forceRemove && this.game.servantManager.getServantDieForTreasureCardSelection(id.replace('treasure-card-', '')).length > 0) {
                                dojo.addClass($(id), 'has-dice-selection')
                            } else {
                                dojo.removeClass($(id), 'has-dice-selection')
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

                    // If no dice already on card, or no dice selection assigned to it always true
                    if (diceOnCard.length === 0 || diceSelectionOnCard.length === 0) {
                        return true;
                    } else {
                        const totalEffortOnCard = diceOnCard.reduce((sum, a) => sum + Number(a['location_arg']), 0);
                        const selectedEffortOnCard = diceSelectionOnCard.length * diceSelectionOnCard[0].location_arg;
                        return selectedEffortOnCard > totalEffortOnCard;
                    }
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

                getSelectedTreasureCardsInPlayerArea() {
                    const selectedTreasureCards = dojo.query('.player-treasure-area .treasure-card.selected');
                    return selectedTreasureCards.map(e => e.id).map(id => id.replace('treasure-card-', ''));
                },

                getSelectedTreasureCardsInDisplay() {
                    const selectedTreasureCards = dojo.query('#treasure-cards-display .treasure-card.selected');
                    return selectedTreasureCards.map(e => e.id).map(id => id.replace('treasure-card-', ''));
                },

                moveTreasureCardToDiscard(cardId) {
                    this.removeTreasureCardFromZones(cardId);
                    this.cardDiscard.placeInZone(`treasure-card-${cardId}`)
                },

                moveTreasureCardToPlayerArea(card, playerId) {
                    this.removeTreasureCardFromZones(card.id);
                    const newZoneHeight = (this.game.cardHeight + (this.playerAreas[playerId][card.type].getItemNumber() * 75));
                    dojo.style(`player-${playerId}-treasure-${card.type}`, 'min-height', newZoneHeight + 'px');
                    dojo.style(`player-${playerId}-treasure-${card.type}`, 'display', 'block');
                    this.playerAreas[playerId][card.type].placeInZone(`treasure-card-${card.id}`)
                },

                removeTreasureCardFromZones(cardId) {
                    const id = `treasure-card-${cardId}`;
                    this.cardDisplay.removeFromZone(id, false);
                    Object.values(this.playerAreas).forEach(playerArea => Object.values(playerArea).forEach(zone => zone.removeFromZone(id, false)));
                },

                renderTooltip(card) {
                    const info = [];
                    const valueDisplay = card.value === 'back' ? '?' : card.value;
                    info.push('<small>'+_('Type:')+'&nbsp;</small><em>'+_(card.type)+'</em>');
                    info.push('<small>'+_('Value:')+'&nbsp;</small><em>' + valueDisplay + '</em>');

                    if (card.location.startsWith("player_area_")) {
                        if (card.location.endsWith(this.game.player_id)) {
                            const faceUpDisplay = card['face_up'] === '1' ? _('Yes') : _('No')
                            info.push('<small>'+_('Value publicly known:')+'&nbsp;</small><em>' + faceUpDisplay + '</em>');
                        }
                        const flippedDisplay = card.flipped === '1' ? _('Yes') : _('No')
                        info.push('<small>'+_('Flipped:')+'&nbsp;</small><em>' + flippedDisplay + '</em>');
                    }

                    return this.game.format_block('jstpl_treasure_card_tooltip', {
                        ...card,
                        text: info.join("<br />")
                    });
                },

                renderDiscardPileTooltip() {
                    const cardsInDiscard = dojo.query('#treasure-cards-discard .treasure-card')

                    const title = '<p><strong>' + _('Discard Pile:') +'</strong></p>';

                    const tooltip = this.game.format_block('jstpl_discard_pile_tooltip', {
                        content: title + cardsInDiscard.map(card => `<div class="${card.className} small" ></div>`).join('')
                    });

                    this.game.addTooltipHtml(`treasure-cards-discard`, tooltip, 800);
                },

                createTreasureCard(card, classNames) {
                    return this.game.format_block('jstpl_treasure_card', {
                        ...card,
                        classNames,
                    });
                },

                // Click Handlers
                onDecreaseDiceClicked(event) {
                    if (this.claimTreasureMode) {
                        console.log("TreasureCardManager#onDecreaseDiceClicked")
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
                        console.log("TreasureCardManager#onIncreaseDiceClicked")
                        const cardId = event.currentTarget.dataset.id;
                        event.preventDefault();
                        event.stopPropagation();

                        const servantDieAlreadyInSelection = this.game.servantManager.getServantDieForTreasureCardSelection(cardId);
                        if (servantDieAlreadyInSelection.length > 0 && servantDieAlreadyInSelection[0].location_arg < 6) {
                            const newValue = servantDieAlreadyInSelection[0].location_arg + 1;
                            servantDieAlreadyInSelection.forEach(die => this.game.servantManager.setServantDieValue(die.id, newValue))
                            this.updateInvalid();
                        }
                    }
                },

                onTreasureCardClicked(event) {
                    console.log("TreasureCardManager#onTreasureCardClicked")
                    const cardId = event.currentTarget.dataset.id;
                    event.preventDefault();
                    event.stopPropagation();

                    const elementId = `treasure-card-${cardId}`;

                    if (this.claimTreasureMode && (dojo.hasClass($(elementId), 'selection') || dojo.hasClass($(elementId), 'has-dice-selection'))) {
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
                    } else if(this.selectTreasureModeDisplay.active && this.cardDisplay.getAllItems().includes(`treasure-card-${cardId}`)) {

                        if (dojo.hasClass($(elementId), 'selectable')) {
                            const nrOfCardsSelected = dojo.query('#treasure-cards-display .treasure-card.selected').length;
                            if (dojo.hasClass($(elementId), 'selected')) {
                                dojo.removeClass($(elementId), 'selected');
                            } else if (nrOfCardsSelected < Number(this.selectTreasureModeDisplay.nrOfCardsToSelect)) {
                                dojo.addClass($(elementId), 'selected');
                            } else {
                                this.game.showMessage(dojo.string.substitute( _("You can only select ${i} treasure cards"), {i: Number(this.selectTreasureModeDisplay.nrOfCardsToSelect)} ), 'error');
                            }
                        }
                    } else if(this.selectTreasureModePlayerArea.active) {
                        const elementId = `treasure-card-${cardId}`;
                        if (dojo.hasClass($(elementId), 'selectable')) {
                            const nrOfCardsSelected = dojo.query('.player-treasure-area .treasure-card.selected').length;
                            if (dojo.hasClass($(elementId), 'selected')) {
                                dojo.removeClass($(elementId), 'selected');
                            } else if (nrOfCardsSelected < Number(this.selectTreasureModePlayerArea.nrOfCardsToSelect)) {
                                dojo.addClass($(elementId), 'selected');
                            } else {
                                this.game.showMessage(dojo.string.substitute( _("You can only select ${i} treasure cards"), {i: Number(this.selectTreasureModePlayerArea.nrOfCardsToSelect)} ), 'error');
                            }
                        }
                    }
                },
            });
    }
);
