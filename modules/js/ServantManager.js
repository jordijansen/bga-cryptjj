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
            'crypt.ServantManager',
            null, {
                servantSize: 44,
                game: null,
                playerAreas: {},
                displayTreasureCardSelectionAreas: {},
                displayTreasureCardAreas: {},
                exhaustedArea: null,
                selectServantDiceMode: {active: false},

                constructor(game) {
                    this.game = game;
                },

                setup() {
                    console.log("ServantManager#setup")
                    // Set-up servant dice
                    dojo.place(this.game.format_block('jstpl_exhausted_area', {
                        "exhaustedServantsAreaText": _('Exhausted <br /> Servants')
                    }), $('exhausted-servants-wrapper'))

                    this.game.addTooltip('exhausted-servants-background', _('Each die that rolls less than its effort value is exhausted and placed here'), '');


                    this.exhaustedArea = new ebg.zone();
                    this.exhaustedArea.create(this.game, $('exhausted-servants'), this.servantSize, 48);

                    this.setupDisplayZones(this.game.gamedatas.treasureCards.filter(card => card.location === 'display'));

                    Object.values(this.game.gamedatas.players).forEach(player => {
                        this.playerAreas[player.player_id] = new ebg.zone();
                        this.playerAreas[player.player_id].create(this.game, dojo.query(`#player-area-${player.player_id} .dice-placement-area`)[0], this.servantSize, this.servantSize);
                    })

                    this.game.gamedatas.servantDice.forEach(servantDie => {
                        const servantDieElement = this.game.format_block('jstpl_die', {
                            "id": servantDie.id,
                            "type": servantDie.type,
                            "color": servantDie.type_arg
                        });
                        // This is just a temp placement, die will be moved to correct zone
                        dojo.place(servantDieElement, $('exhausted-servants'))
                    })

                    this.moveServantDiceToLocations(this.game.gamedatas.servantDice);
                },

                setupDisplayZones(cards) {
                    this.displayTreasureCardSelectionAreas = {};
                    this.displayTreasureCardAreas = {};

                    cards.forEach(card => {
                        this.displayTreasureCardSelectionAreas[card.id] = new ebg.zone();
                        this.displayTreasureCardSelectionAreas[card.id].create(this.game, dojo.query(`#treasure-card-${card.id} .dice-selection-area`)[0], this.servantSize, this.servantSize);

                        this.displayTreasureCardAreas[card.id] = new ebg.zone();
                        this.displayTreasureCardAreas[card.id].create(this.game, dojo.query(`#treasure-card-${card.id} .dice-placement-area`)[0], this.servantSize, this.servantSize);
                    })
                },

                setServantDieValue(dieId, value) {
                    const servantDie = this.game.gamedatas.servantDice.find(die => die.id === dieId);
                    servantDie.location_arg = Number(value);

                    const servantDieElement = $(`servant-die-${servantDie.id}`);

                    for (var i = 1; i <= 6; i++) {
                        dojo.removeClass(servantDieElement, 'value-' + i)
                        if (servantDie.location_arg === i) {
                            dojo.addClass(servantDieElement, 'value-' + i)
                        }
                    }
                },

                moveServantDieToPlayerArea(servantId, playerId, value) {
                    this.removeServantDieFromZones(servantId);
                    this.playerAreas[playerId].placeInZone(`servant-die-${servantId}`);
                    this.setServantDieValue(servantId, value);
                },

                moveServantDieToTreasureCardSelectionArea(servantId, cardId, value) {
                    this.removeServantDieFromZones(servantId);
                    this.displayTreasureCardSelectionAreas[cardId].placeInZone(`servant-die-${servantId}`)
                    this.setServantDieValue(servantId, value);
                },

                moveServantDieToTreasureCard(servantId, cardId, value) {
                    this.removeServantDieFromZones(servantId);
                    this.displayTreasureCardAreas[cardId].placeInZone(`servant-die-${servantId}`)
                    this.setServantDieValue(servantId, value);
                },

                moveServantDieToExhaustedArea(servantId, value) {
                    this.removeServantDieFromZones(servantId);
                    this.exhaustedArea.placeInZone(`servant-die-${servantId}`)
                    this.setServantDieValue(servantId, value);
                    dojo.connect($(`servant-die-${servantId}`), 'onclick', this, 'onServantDieClicked')
                },

                getServantDieForTreasureCardSelection(cardId) {
                    return this.displayTreasureCardSelectionAreas[cardId].getAllItems()
                        .map(id => id.replace('servant-die-', ''))
                        .map(id => this.game.gamedatas.servantDice.find(die => die.id === id));
                },

                getServantDieForTreasureCard(cardId) {
                    return this.displayTreasureCardAreas[cardId].getAllItems()
                        .map(id => id.replace('servant-die-', ''))
                        .map(id => this.game.gamedatas.servantDice.find(die => die.id === id));
                },

                getServantDieInPlayerArea(playerId) {
                    return this.playerAreas[playerId].getAllItems()
                        .map(id => id.replace('servant-die-', ''))
                        .map(id => this.game.gamedatas.servantDice.find(die => die.id === id));
                },

                getServantDieInExhaustedArea(playerId) {
                    return this.exhaustedArea.getAllItems()
                        .map(id => id.replace('servant-die-', ''))
                        .map(id => this.game.gamedatas.servantDice.find(die => die.id === id))
                        .filter(die => Number(die.type) === playerId);
                },

                moveServantDiceToLocations(servantDice) {
                    servantDice.forEach(servantDie => {
                        if (servantDie.location === 'player_area') {
                            this.moveServantDieToPlayerArea(servantDie.id, servantDie.type, servantDie.location_arg);
                        } else if (servantDie.location.startsWith('treasure_card_')) {
                            const treasureCardId = servantDie.location.replace('treasure_card_', '');
                            this.moveServantDieToTreasureCard(servantDie.id, treasureCardId, servantDie.location_arg)
                        } else if (servantDie.location === 'exhausted') {
                            this.moveServantDieToExhaustedArea(servantDie.id, servantDie.location_arg);
                        }
                    })
                },

                removeServantDieFromZones(servantId) {
                    const id = `servant-die-${servantId}`;
                    this.exhaustedArea.removeFromZone(id, false);
                    Object.values(this.game.gamedatas.players).forEach(player => {
                        this.playerAreas[player.player_id].removeFromZone(id, false);
                    })

                    Object.values(this.displayTreasureCardSelectionAreas).forEach(zone => zone.removeFromZone(id, false))
                    Object.values(this.displayTreasureCardAreas).forEach(zone => zone.removeFromZone(id, false))
                },

                enterSelectServantDiceMode(diceForSelection) {
                    this.selectServantDiceMode = {active: true, diceForSelection}
                    if (diceForSelection && diceForSelection.length > 0) {
                        diceForSelection.filter(die => this.exhaustedArea.getAllItems().includes(`servant-die-${die.id}`)) // We only allow selection in exhausted area
                            .forEach(die => {
                                dojo.addClass($(`servant-die-${die.id}`), 'selectable')
                        })
                    }
                },

                exitSelectServantDiceMode() {
                    this.selectServantDiceMode = {active: false }
                    this.exhaustedArea.getAllItems().forEach(id => {
                        dojo.removeClass($(id), 'selectable')
                        dojo.removeClass($(id), 'selected')
                    });
                    dojo.empty('exhausted-servants-text');
                },

                onServantDieClicked(event) {
                    console.log("ServantManager#onServantDieClicked")
                    const dieId = event.currentTarget.dataset.id;
                    event.preventDefault();
                    event.stopPropagation();

                    if(this.selectServantDiceMode.active) {
                        const elementId = `servant-die-${dieId}`;
                        if (dojo.hasClass($(elementId), 'selectable')) {
                            const nrOfCardsSelected = dojo.query('.servant-die.selected').length;
                            if (dojo.hasClass($(elementId), 'selected')) {
                                dojo.removeClass($(elementId), 'selected');
                                dojo.empty('exhausted-servants-text');
                            } else if (nrOfCardsSelected < 1) {
                                dojo.addClass($(elementId), 'selected');
                                const servantDie = this.selectServantDiceMode.diceForSelection.find(die => die.id === die.id);
                                const helpText = dojo.string.substitute( _("Roll higher or equal to ${i} to recover"), {i: servantDie.effort})
                                dojo.place(`<p>${helpText}</p>`, 'exhausted-servants-text');
                            } else {
                                this.game.showMessage(dojo.string.substitute( _("You can only select 1 servant die")), 'error');
                            }
                        }
                    }
                },

                getSelectedServantDice() {
                    const selectedTreasureCards = dojo.query('.servant-die.selected');
                    return selectedTreasureCards.map(e => e.id).map(id => id.replace('servant-die-', ''));
                }
    });
    }
);
