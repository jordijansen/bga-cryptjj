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
                    }
                },
            });
    }
);
