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
                playerTorchCardAreas: {},
                leaderPlayer: null,
                lightsOutPlayer: null,

                constructor(game) {
                    this.game = game;
                },

                setup() {
                    console.log("PlayerManager#setup")
                    // Set-up player areas
                    let thisPlayerArea;
                    const playerAreas = [];
                    Object.entries(this.game.gamedatas.players).forEach(([playerId, player]) => {
                        this.playerTorchCardAreas[playerId] = new ebg.zone();

                        const playerBoardDiv = $(`player_board_${playerId}`);
                        const torchCardsElement = this.game.format_block('jstpl_torch_card_container', {playerId});
                        dojo.place(torchCardsElement, playerBoardDiv);

                        const torchCardContainerId = $(`torch-cards-${playerId}`);
                        this.playerTorchCardAreas[playerId].create(this.game, torchCardContainerId, this.game.cardSmallWidth, this.game.cardSmallHeight);

                        if (Number(this.game.gamedatas.players[playerId].custom_order) === 1) {
                            this.createCard(playerId, 'leader');
                            this.setLeaderCard(playerId);
                        }
                        const lightsOutPlayerNo = Object.keys(this.game.gamedatas.players).length === 2 ? 1 : Object.keys(this.game.gamedatas.players).length;
                        if (Number(this.game.gamedatas.players[playerId].custom_order) === lightsOutPlayerNo) {
                            this.createCard(playerId, 'lights-out');
                            this.setLightsOutCard(playerId);
                        }

                        const playerArea = this.game.format_block('jstpl_player_area', {
                            "player_id": player.player_id,
                            "color": player.color,
                            "name": player.name,
                        });
                        if (Number(player.player_id) === this.game.player_id) {
                            playerAreas.unshift(playerArea);
                        } else {
                            playerAreas.push(playerArea);
                        }

                        const scoreTooltipText = _('Scores shown are based on public information, face-down collected treasure cards are not reflected in your score. Hence your final score might result in a higher score.');
                        this.game.addTooltip(`player_score_${playerId}`, scoreTooltipText, '');
                        this.game.addTooltip(`icon_point_${playerId}`, scoreTooltipText, '');
                    })

                    playerAreas.forEach(playerArea => dojo.place(playerArea, "player-areas-row"))
                },

                getPlayerCount() {
                    return Object.keys(this.game.gamedatas.players).length;
                },

                createCard(playerId, type) {
                    const torchCardContainerId = $(`torch-cards-${playerId}`);
                    const card = this.game.format_block('jstpl_torch_card', {type});
                    dojo.place(card, torchCardContainerId);
                },

                setLeaderCard(playerId) {
                    this.removeCardFromZones('leader-card');
                    this.playerTorchCardAreas[playerId].placeInZone('leader-card');

                    this.game.addTooltipHtml('leader-card', this.renderTorchCardTooltip('leader'), 800);
                },

                setLightsOutCard(playerId) {
                    this.removeCardFromZones('lights-out-card');
                    this.playerTorchCardAreas[playerId].placeInZone('lights-out-card');

                    this.game.addTooltipHtml('lights-out-card', this.renderTorchCardTooltip('lights-out'), 800);
                },

                removeCardFromZones(cardId) {
                    Object.values(this.playerTorchCardAreas).forEach((zone) => {
                       zone.removeFromZone(cardId, false)
                    });
                },

                hasLightsOutCard(playerId) {
                    return this.playerTorchCardAreas[playerId].getAllItems().includes('lights-out-card');
                },

                hasLeaderCard(playerId) {
                    return this.playerTorchCardAreas[playerId].getAllItems().includes('leader-card');
                },

                hasPlayedBeforeThisRound() {
                    return this.playedBeforeThisRound === true;
                },

                hasUsedManuscriptBThisRound() {
                    return this.usedManuscriptBThisRound === true;
                },

                updateScore(score = {player_id: 1, player_score: 1}) {
                    this.game.scoreCtrl[score.player_id].setValue(score.player_score);
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
