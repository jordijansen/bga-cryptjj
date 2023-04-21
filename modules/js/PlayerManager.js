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
            'crypt.PlayerManager',
            null, {
                game: null,
                playedBeforeThisRound: false,
                usedManuscriptBThisRound: false,
                leaderPlayer: null,
                lightsOutPlayer: null,

                constructor(game) {
                    this.game = game;
                },

                setup() {
                    console.log("PlayerManager#setup")
                    // Set-up player areas
                    let thisPlayerArea;
                    const otherPlayerAreas = [];
                    Object.entries(this.game.gamedatas.players).forEach(([playerId, player]) => {
                        const playerArea = this.game.format_block('jstpl_player_area', {
                            "id": player.id,
                            "color": player.color,
                            "name": player.name,
                        });
                        if (Number(player.id) === this.game.player_id) {
                            thisPlayerArea = playerArea;
                        } else {
                            otherPlayerAreas.push(playerArea);
                        }

                        if (this.game.gamedatas.players[playerId].has_torch_card_leader === '1') {
                            this.setLeaderCard(playerId);
                        }
                        if (this.game.gamedatas.players[playerId].has_torch_card_lights_out === '1') {
                            this.setLightsOutCard(playerId);
                        }
                    })

                    dojo.place(thisPlayerArea, "player-areas-row")
                    otherPlayerAreas.forEach(playerArea => dojo.place(playerArea, "player-areas-row"))
                },

                getPlayerCount() {
                    return Object.keys(this.game.gamedatas.players).length;
                },

                setLeaderCard(playerId) {
                    const player_board_div = $('player_board_' + playerId);
                    const card = this.game.format_block('jstpl_torch_card_leader');
                    dojo.destroy('leader-card');
                    dojo.place(card, player_board_div);
                    this.game.addTooltipHtml('leader-card', this.renderTorchCardTooltip('leader'), 800);
                    this.leaderPlayer = playerId;
                },

                setLightsOutCard(playerId) {
                    const player_board_div = $('player_board_' + playerId);
                    const card = this.game.format_block('jstpl_torch_card_lights_out');
                    dojo.destroy('lights-out-card');
                    dojo.place(card, player_board_div);
                    this.game.addTooltipHtml('lights-out-card', this.renderTorchCardTooltip('lights-out'), 800);
                    this.lightsOutPlayer = playerId;
                },

                hasLightsOutCard(playerId) {
                    return this.lightsOutPlayer === playerId + '';
                },

                hasLeaderCard(playerId) {
                    return this.leaderPlayer === playerId + '';
                },

                hasPlayedBeforeThisRound() {
                    return this.playedBeforeThisRound === true;
                },

                hasUsedManuscriptBThisRound() {
                    return this.usedManuscriptBThisRound === true;
                },

                renderTorchCardTooltip(type) {
                    const text = type === 'leader' ?
                        _('Starting with the Leader, each player gets one turn to claim Treasure cards by placing Servant dice on them or recover their Servant dice. In a 2-player game, the Leader also has the Lights Out card and therefore gets a second turn to claim or recover.') :
                        _('The player with the Lights Out card will have the last turn. On the last turn, this player can only place Servants on one Treasure card. In a 2-player game, the Leader also has the Lights Out card and therefore gets a second turn to claim or recover.');
                    return this.game.format_block('jstpl_torch_card_tooltip', {type, text});
                },
            });
    }
);
