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
            'crypt.CollectorCardManager',
            null, {
                game: null,
                collectorCards: {},
                activateCollectorMode: false,

                constructor(game) {
                    this.game = game;
                },

                setup() {
                    console.log("CollectorCardManager#setup")

                    // Set-up collector cards display
                    this.collectorCards = new ebg.zone();
                    this.collectorCards.create(this.game, $('collector-cards'), this.game.cardWidth, this.game.cardHeight);
                    this.collectorCards.item_margin = 10;

                    for (const card of this.game.gamedatas.collectors) {
                        const collectorCard = this.game.format_block('jstpl_collector_card', {
                            ...card
                        });
                        dojo.place(collectorCard, 'collector-cards')
                        this.collectorCards.placeInZone(`collector-card-${card.id}`);
                        this.game.addTooltip(`collector-card-${card.id}`, card.description_translated, '');
                        dojo.connect($(`collector-card-${card.id}`), 'onclick', this, 'onCollectorCardClicked')
                    }
                },

                getPossibleCollectorWithAbilityType(abilityType) {
                    const result = [];
                    for (const card of this.game.gamedatas.collectors) {
                        if (card.ability_type === abilityType) {
                            const flippableTreasureCardIds = this.game.treasureCardManager.getTreasureCardsInPlayerAreaOfType(this.game.player_id, card.treasure_type)
                                .filter(id => this.game.treasureCardManager.isTreasureCardFaceDown(id, card.treasure_type));
                            if (flippableTreasureCardIds.length >= card.nr_of_cards_to_flip) {
                                if (card.id === 'remains-A') {
                                    // remains-A lets you recover a servant die, useless if you have no exhausted servant dice
                                    console.log(this.game.servantManager.getServantDieInExhaustedArea(this.game.player_id));
                                    if (this.game.servantManager.getServantDieInExhaustedArea(this.game.player_id).length > 0) {
                                        result.push(card);
                                    }
                                } else if (card.id === 'manuscript-B') {
                                    // manuscript-B lets you view face down cards in the display, useless if you've already viewed those this round
                                    if (!this.game.playerManager.hasUsedManuscriptBThisRound()) {
                                        result.push(card);
                                    }
                                } else {
                                    result.push(card);
                                }
                            }
                        }
                    }
                    return result;
                },

                enterActivateCollectorMode(abilityType) {
                    this.activateCollectorMode = true;

                    const cardsThatCanBeActivated = this.getPossibleCollectorWithAbilityType(abilityType);
                    for (const card of cardsThatCanBeActivated) {
                        dojo.addClass($(`collector-card-${card.id}`), 'selectable')
                        if (cardsThatCanBeActivated.length === 1) {
                            this.selectCollectorCard(card.id);
                        }
                    }
                },

                exitActivateCollectorMode() {
                    this.activateCollectorMode = false;
                    for (const card of this.game.gamedatas.collectors) {
                        const cardElement = $(`collector-card-${card.id}`);
                        dojo.removeClass(cardElement, 'selectable')
                        dojo.removeClass(cardElement, 'selected')
                    }
                },

                getSelectedCollector() {
                    const selectedCollectors = dojo.query('.collector-card.selected');
                    return selectedCollectors.length === 1 ? selectedCollectors[0].id.replace(`collector-card-`, '') : undefined;
                },

                onCollectorCardClicked(event) {
                    event.preventDefault();
                    event.stopPropagation();

                    this.selectCollectorCard(event.target.dataset.id)
                },

                selectCollectorCard(cardId) {
                    console.log('selectCollectorCard = ' + cardId)

                    const selected = this.game.gamedatas.collectors.find(c => c.id === cardId);

                    if (this.activateCollectorMode) {
                        // Only one collector card should be selected at a time
                        for (const card of this.game.gamedatas.collectors) {
                            const cardElement = $(`collector-card-${card.id}`);
                            dojo.removeClass(cardElement, 'selected')
                        }
                        dojo.addClass($(`collector-card-${selected.id}`), 'selected')

                        const collectorType = selected.id.replace('-A', '').replace('-B', '')
                        this.game.treasureCardManager.enterSelectTreasureMode(collectorType, selected.nr_of_cards_to_flip)
                        this.activateCollectorMode = false;
                        this.game.gamedatas.gamestate.descriptionmyturn = selected.name_translated + ': ' + dojo.string.substitute( _("flips ${i} ${type} treasure card(s)"), {i: Number(selected.nr_of_cards_to_flip), type: selected.treasure_type} );
                        this.game.gamedatas.gamestate.description = this.game.gamedatas.gamestate.descriptionmyturn;
                        this.game.updatePageTitle();
                    }
                }
            });
    }
);
