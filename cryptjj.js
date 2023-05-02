/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * CryptJj implementation : © Jordi Jansen <thestartplayer@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * cryptjj.js
 *
 * CryptJj user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define(["dojo",
        "dojo/_base/declare",
        "ebg/core/gamegui",
        "ebg/counter",
        "ebg/stock",
        "ebg/zone",

        g_gamethemeurl + 'modules/js/ActionManager.js',
        g_gamethemeurl + 'modules/js/DeckManager.js',
        g_gamethemeurl + 'modules/js/TreasureCardManager.js',
        g_gamethemeurl + 'modules/js/PlayerManager.js',
        g_gamethemeurl + 'modules/js/ServantManager.js',
        g_gamethemeurl + 'modules/js/CollectorCardManager.js',
    ],
function (dojo, declare) {
    return declare("bgagame.cryptjj", ebg.core.gamegui, {
        constructor: function () {
            console.log('cryptjj constructor');

            this.cardWidth = 178;
            this.cardHeight = 261;
            this.cardSmallWidth = 71;
            this.cardSmallHeight = 104;

            this.actionManager = new crypt.ActionManager(this);
            this.playerManager = new crypt.PlayerManager(this);
            this.deckManager = new crypt.DeckManager(this);
            this.treasureCardManager = new crypt.TreasureCardManager(this);
            this.servantManager = new crypt.ServantManager(this);
            this.collectorCardManager = new crypt.CollectorCardManager(this);
            // Here, you can init the global variables of your user interface
            // Example:
            // this.myGlobalValue = 0;

            this.gameStates = {
                playerTurn: 'playerTurn',
                beforeClaimPhaseActivateCollectors: 'beforeClaimPhaseActivateCollectors',
                afterCollectTreasureActivateCollectors: 'afterCollectTreasureActivateCollectors',
                claimTreasure: 'claimTreasure', // Client Side only state
                activateCollector: 'activateCollector' // Client Side only state
            }

            this.gameActions = {
                claimTreasure: 'claimTreasure',
                recoverServants: 'recoverServants',
                activateCollector: 'activateCollector'
            }

        },

        /*
            setup:
            
            This method must set up the game user interface according to current game situation specified
            in parameters.
            
            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)
            
            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */

        setup: function (gameData) {
            console.log("Starting game setup");
            console.dir(gameData);

            this.deckManager.setup(gameData);
            this.playerManager.setup(gameData);
            this.treasureCardManager.setup(gameData);
            this.servantManager.setup(gameData);
            this.collectorCardManager.setup(gameData);

            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            console.log("Ending game setup");
        },


        ///////////////////////////////////////////////////
        //// Game & client states

        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function (stateName, args) {
            switch (stateName) {

                /* Example:

                case 'myGameState':

                    // Show some HTML block at this game state
                    dojo.style( 'my_html_block_id', 'display', 'block' );

                    break;
               */

                case this.gameStates.playerTurn:
                    this.playerManager.playedBeforeThisRound = args.args.playedBeforeThisRound;
                    this.playerManager.usedManuscriptBThisRound = args.args.usedManuscriptBThisRound;
                    break;
                case this.gameStates.claimTreasure:
                    this.treasureCardManager.enterClaimTreasureMode();
                    break;
                case this.gameStates.activateCollector:
                    this.collectorCardManager.enterActivateCollectorMode(args.args.possibleCollectors, args.args.servantDiceForReRoll);
                    break;
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function (stateName) {
            console.log('Leaving state: ' + stateName);

            switch (stateName) {

                /* Example:

                case 'myGameState':

                    // Hide the HTML block we are displaying only during this game state
                    dojo.style( 'my_html_block_id', 'display', 'none' );

                    break;
               */


                case this.gameStates.claimTreasure:
                    this.treasureCardManager.exitClaimTreasureMode(false);
                    break;
                case this.gameStates.activateCollector:
                    this.treasureCardManager.exitSelectTreasureMode();
                    this.servantManager.exitSelectServantDiceMode();
                    this.collectorCardManager.exitActivateCollectorMode();
                    break;
            }
        },

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function (stateName, args) {
            console.log('onUpdateActionButtons: ' + stateName);

            // Any Time Actions
            if (!this.isSpectator) {
                switch (stateName) {
                    case this.gameStates.playerTurn:
                    case this.gameStates.beforeClaimPhaseActivateCollectors:
                    case this.gameStates.afterCollectTreasureActivateCollectors:
                        const possibleCollectors = this.collectorCardManager.getPossibleCollectors(stateName);
                        if (possibleCollectors.length > 0) {
                            this.addActionButton('activate_collector', _('Activate Collectors'), (evt)=> this.enterActivateCollectorMode(evt,possibleCollectors, args?.servantDiceForReRoll), null, false, 'red');
                        }
                        break;
                    case this.gameStates.activateCollector:
                        this.addActionButton('confirm_activate_collector', _('Confirm'), 'confirmActivateCollector');
                        this.addActionButton('undo_activate_collector', _('Cancel'), 'undoActivateCollector', null, false, 'gray');
                        break;
                }
            }

            if (this.isCurrentPlayerActive()) {
                switch (stateName) {
                    /*
                                     Example:

                                     case 'myGameState':

                                        // Add 3 action buttons in the action status bar:

                                        this.addActionButton( 'button_1_id', _('Button 1 label'), 'onMyMethodToCall1' );
                                        this.addActionButton( 'button_2_id', _('Button 2 label'), 'onMyMethodToCall2' );
                                        this.addActionButton( 'button_3_id', _('Button 3 label'), 'onMyMethodToCall3' );
                                        break;
                    */
                    case this.gameStates.playerTurn:
                        this.addActionButton('claim_treasure_button', _('Claim Treasure'), 'enterClaimTreasureMode');
                        this.addActionButton('recover_servants_button', _('Recover Servants'), 'recoverServants');
                        break;
                    case this.gameStates.claimTreasure:
                        this.addActionButton('confirm_claim_treasure_state', _('Confirm'), 'confirmClaimTreasure');
                        this.addActionButton('undo_claim_treasure_state', _('Cancel'), 'undoClaimTreasure', null, false, 'gray');
                        break;
                    case this.gameStates.beforeClaimPhaseActivateCollectors:
                    case this.gameStates.afterCollectTreasureActivateCollectors:
                        this.addActionButton('end_before_claim_phase_activate_collectors', _("End Turn"), 'endTurn');
                        break;

                }
            }
        },

        ///////////////////////////////////////////////////
        //// Utility methods

        /*
        
            Here, you can defines some utility methods that you can use everywhere in your javascript
            script.
        
        */

        /* @Override */
        format_string_recursive : function format_string_recursive(log, args) {
            try {
                if (log && args && !args.processed) {
                    args.processed = true;

                    // list of special keys we want to replace with images
                    const keys = ['icon_treasure','icon_coin', 'icon_dice', 'icon_torch'];

                    keys.forEach(key => {
                        Object.keys(args)
                            .filter(k => k.startsWith(key))
                            .forEach(k => args[k] = this.getIcon(k, args))
                    })
                }
            } catch (e) {
                console.error(log,args,"Exception thrown", e.stack);
            }
            return this.inherited({callee: format_string_recursive}, arguments);
        },

        getIcon : function(key, args) {
            if (key.startsWith('icon_treasure')) {
                return this.format_block('jstpl_icon_treasure',{type: args[key]});
            } else if (key.startsWith('icon_coin')) {
                return this.format_block('jstpl_icon_coin',{value: args[key] === 'back' ? '?' : args[key]});
            } else if (key.startsWith('icon_dice')) {
                return args[key]
                    .map(die => this.format_block('jstpl_icon_dice',{color: die.type_arg, value: die.location_arg}))
                    .join(' ')
            } else if (key.startsWith('icon_torch')) {
                return this.format_block('jstpl_icon_torch',{type: args['icon_torch']});
            }
            return key;
        },


        ///////////////////////////////////////////////////
        //// Player's action

        /*
        
            Here, you are defining methods to handle player's action (ex: results of mouse click on 
            game objects).
            
            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server
        
        */

        /* Example:
        
        onMyMethodToCall1: function( evt )
        {
            console.log( 'onMyMethodToCall1' );

            // Preventing default browser reaction
            dojo.stopEvent( evt );

            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if( ! this.checkAction( 'myAction' ) )
            {   return; }

            this.ajaxcall( "/cryptjj/cryptjj/myAction.html", {
                                                                    lock: true,
                                                                    myArgument1: arg1,
                                                                    myArgument2: arg2,
                                                                    ...
                                                                 },
                         this, function( result ) {

                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)

                         }, function( is_error) {

                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                         } );
        },
        
        */

        enterClaimTreasureMode: function (evt) {
            console.log('enterClaimTreasureMode');

            // Preventing default browser reaction
            dojo.stopEvent(evt);

            if (!this.checkAction(this.gameActions.claimTreasure)) {
                return;
            }

            if (this.servantManager.getServantDieInPlayerArea(this.player_id).length > 0) {
                this.setClientState(this.gameStates.claimTreasure, {
                    descriptionmyturn: _("${you} must claim treasure using your servants and choose their effort value")
                })
            } else {
                this.showMessage(_('You have no servant dice available, recover servants first'), 'error');
            }
        },


        undoClaimTreasure: function (evt) {
            console.log('undoClaimTreasure');

            // Preventing default browser reaction
            dojo.stopEvent(evt);

            this.treasureCardManager.exitClaimTreasureMode(true);
            this.restoreServerGameState();
        },

        confirmClaimTreasure: function (evt) {
            console.log('confirmClaimTreasure');

            // Preventing default browser reaction
            dojo.stopEvent(evt);

            const currentSelection = this.treasureCardManager.getCurrentSelection();
            this.actionManager.claimTreasure(currentSelection)
        },

        recoverServants: function (evt)
        {
            console.log('recoverServants');

            // Preventing default browser reaction
            dojo.stopEvent(evt);

            const nrOfServantDiceToRecover = this.servantManager.getServantDieInExhaustedArea(this.player_id).length;
            const confirmMessage = dojo.string.substitute( _('You will recover ${nrOfServantDiceToRecover} Servant(s). Recovering Servants ends your turn'), { nrOfServantDiceToRecover });

            this.confirmationDialog(
                confirmMessage,
                () => {
                    this.actionManager.recoverServants();
                }
            );
        },

        enterActivateCollectorMode: function (evt, possibleCollectors, servantDiceForReRoll)
        {
            console.log('enterActivateCollectorMode')

            // Preventing default browser reaction
            dojo.stopEvent(evt);

            this.setClientState(this.gameStates.activateCollector, {
                args: {possibleCollectors, servantDiceForReRoll},
                description: _("Select a collector to activate"),
                descriptionmyturn: _("Select a collector to activate")
            })
        },

        confirmActivateCollector: function (evt)
        {
            console.log('confirmActivateCollector')

            const collectorId = this.collectorCardManager.getSelectedCollector();
            if (collectorId) {
                const treasureCardIdsToFlip = this.treasureCardManager.getSelectedTreasureCardsInPlayerArea();
                const treasureCardIdsSelected = this.treasureCardManager.getSelectedTreasureCardsInDisplay();
                const servantDiceSelected = this.servantManager.getSelectedServantDice();
                if (treasureCardIdsToFlip.length > 0) {
                    this.actionManager.activateCollector(collectorId, treasureCardIdsToFlip, treasureCardIdsSelected, servantDiceSelected);
                } else {
                    this.showMessage( _('You need to select treasure card(s) to flip'), 'error');
                }
            } else {
                this.showMessage( _('You need to select a collector to activate'), 'error');
            }
        },

        undoActivateCollector: function (evt)
        {
            console.log('undoActivateCollector')

            // Preventing default browser reaction
            dojo.stopEvent(evt);

            this.restoreServerGameState();
        },

        endTurn: function (evt)
        {
            console.log('undoActivateCollector')

            // Preventing default browser reaction
            if (evt) {
                dojo.stopEvent(evt);
            }

            this.actionManager.endTurn();
        },

        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your cryptjj.game.php file.
        
        */
        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );
            
            dojo.subscribe('treasureCardClaimed', this, 'notif_treasureCardClaimed');
            dojo.subscribe('treasureCardBumped', this, 'notif_treasureCardBumped');
            dojo.subscribe('servantDiceRecovered', this, 'notif_servantDiceRecovered');
            dojo.subscribe('treasureCardDiscarded', this, 'notif_treasureCardDiscarded');
            dojo.subscribe('treasureCardCollected', this, 'notif_treasureCardCollected');
            dojo.subscribe('treasureCardCollectedPrivate', this, 'notif_treasureCardCollectedPrivate');
            dojo.subscribe('treasureCardDisplayUpdated', this, 'notif_treasureCardDisplayUpdated');
            dojo.subscribe('leaderCardPassed', this, 'notif_leaderCardPassed');
            dojo.subscribe('lightsOutCardPassed', this, 'notif_lightsOutCardPassed');
            dojo.subscribe('collectorUsed', this, 'notif_collectorUsed');
            dojo.subscribe('faceDownDisplayCardsRevealedPrivate', this, 'notif_faceDownDisplayCardsRevealedPrivate');
            dojo.subscribe('servantDieReRolled', this, 'notif_servantDieReRolled');
            dojo.subscribe('allCardsFlipped', this, 'notif_allCardsFlipped');

            this.notifqueue.setSynchronous( 'treasureCardClaimed', 1000 );
            this.notifqueue.setSynchronous( 'servantDiceRecovered', 1000 );
            this.notifqueue.setSynchronous( 'treasureCardDiscarded', 1000 );
            this.notifqueue.setSynchronous( 'treasureCardCollected', 1000 );
            this.notifqueue.setSynchronous( 'treasureCardDisplayUpdated', 1000 );
            this.notifqueue.setSynchronous( 'collectorUsed', 1000 );
            this.notifqueue.setSynchronous( 'faceDownDisplayCardsRevealedPrivate', 1000 );
            this.notifqueue.setSynchronous( 'servantDieReRolled', 1000 );

            // Example 1: standard notification handling
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            
            // Example 2: standard notification handling + tell the user interface to wait
            //            during 3 seconds after calling the method in order to let the players
            //            see what is happening in the game.
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
            // 
        },  
        
        // TODO: from this point and below, you can write your game notifications handling methods
        
        /*
        Example:
        
        notif_cardPlayed: function( notif )
        {
            console.log( 'notif_cardPlayed' );
            console.log( notif );

            // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call

            // TODO: play the card in the user interface.
        },
        
        */

        notif_treasureCardClaimed: function( notif = {args: {servantDice: [{id: 1, location: '', location_arg: 3}], bumpedServantDice: [{id: 1, location: '', location_arg: 3}], treasureCard: {id: 1}}} )
        {
            console.log( 'notif_treasureCardClaimed' );

            const treasureCardClaimed = notif['args'];
            console.log( treasureCardClaimed );

            treasureCardClaimed.servantDice.forEach(servantDie => this.servantManager.moveServantDieToTreasureCard(servantDie.id, treasureCardClaimed.treasureCard.id, servantDie.location_arg))
        },

        notif_treasureCardBumped: function( notif = {args: {bumpedServantDice: [{id: 1, location: '', location_arg: 3}], treasureCard: {id: 1}}} )
        {
            console.log( 'notif_treasureCardBumped' );

            const treasureCardBumped = notif['args'];
            console.log( treasureCardBumped );

            Object.values(treasureCardBumped.bumpedServantDice).forEach(servantDie => this.servantManager.moveServantDieToPlayerArea(servantDie.id, servantDie.type, servantDie.location_arg))
        },

        notif_servantDiceRecovered: function( notif = {args: {player_id: 1, player_score: 3, recoveredServantDice: [{id: 1, location: '', location_arg: 3}], treasureCard: {id: 1}}} ) {
            console.log( 'notif_servantDiceRecovered' );

            const servantDiceRecovered = notif['args'];
            console.log( servantDiceRecovered );

            this.playerManager.updateScore(servantDiceRecovered);
            Object.values(servantDiceRecovered.recoveredServantDice).forEach(servantDie => this.servantManager.moveServantDieToPlayerArea(servantDie.id, servantDie.type, servantDie.location_arg))
        },

        notif_treasureCardDiscarded: function( notif = {args: {treasureCard: {id: 1}}} ) {
            console.log( 'notif_treasureCardDiscarded' );

            const treasureCardDiscarded = notif['args'];
            console.log( treasureCardDiscarded );

            this.treasureCardManager.moveTreasureCardToDiscard(treasureCardDiscarded.treasureCard.id);
        },

        notif_treasureCardCollected: function( notif = {args: {player_id: 1, player_score: 3, rolledServantDice: [{id: 1}], treasureCard: {id: 1}}} ) {
            console.log( 'notif_treasureCardCollected' );

            const treasureCardCollected = notif['args'];
            console.log( treasureCardCollected );

            this.playerManager.updateScore(treasureCardCollected);
            this.servantManager.moveServantDiceToLocations(treasureCardCollected.rolledServantDice);
            this.treasureCardManager.renderCardsAndMoveToZone([treasureCardCollected.treasureCard], true);
        },

        notif_treasureCardCollectedPrivate: function( notif = {args: {playerId: 1, player_score: 3, rolledServantDice: [{id: 1}], treasureCard: {id: 1}}} ) {
            console.log( 'notif_treasureCardCollectedPrivate' );

            const treasureCardCollected = notif['args'];
            console.log( treasureCardCollected );

            this.treasureCardManager.renderCardsAndMoveToZone([treasureCardCollected.treasureCard], true);
        },

        notif_treasureCardDisplayUpdated: function( notif = {args: {treasureCards: [], treasureDeck: {}}} ) {
            console.log( 'notif_treasureCardDisplayUpdated' );

            const treasureCardDisplayUpdated = notif['args'];
            console.log( treasureCardDisplayUpdated );

            this.deckManager.update(treasureCardDisplayUpdated.treasureDeck);
            this.treasureCardManager.renderCardsAndMoveToZone(treasureCardDisplayUpdated.treasureCards);
            this.servantManager.setupDisplayZones(treasureCardDisplayUpdated.treasureCards);
        },

        notif_leaderCardPassed: function( notif = {args: {player_id: 1}} ) {
            console.log( 'notif_leaderCardPassed' );

            const leaderCardPassed = notif['args'];
            console.log( leaderCardPassed );

            this.playerManager.setLeaderCard(leaderCardPassed.player_id)
        },

        notif_lightsOutCardPassed: function( notif = {args: {player_id: 1}} ) {
            console.log( 'notif_lightsOutCardPassed' );

            const lightsOutCardPassed = notif['args'];
            console.log( lightsOutCardPassed );

            this.playerManager.setLightsOutCard(lightsOutCardPassed.player_id)
        },

        notif_collectorUsed: function( notif = {args: {player_id: 1, player_score: 3, collector: {}, flippedTreasureCards: []}} ) {
            console.log( 'notif_collectorUsed' );

            const collectorUsed = notif['args'];
            console.log( collectorUsed );

            this.treasureCardManager.renderCardsAndMoveToZone(collectorUsed.flippedTreasureCards, true);

            this.playerManager.updateScore(collectorUsed);

            if (Number(collectorUsed.player_id) === this.player_id) {
                this.restoreServerGameState();
            }
        },

        notif_faceDownDisplayCardsRevealedPrivate: function( notif = {args: {playerId: '1', treasureCards: []}} ) {
            console.log( 'notif_faceDownDisplayCardsRevealedPrivate' );

            const faceDownDisplayCardsRevealed = notif['args'];
            console.log( faceDownDisplayCardsRevealed );

            if (faceDownDisplayCardsRevealed.treasureCards) {
                this.treasureCardManager.renderCardsAndMoveToZone(faceDownDisplayCardsRevealed.treasureCards, true);
            }
        },

        notif_servantDieReRolled: function( notif = {args: {playerId: '1', exhausted: true, servantDie: {id: '1', type: 'playerId', location_arg: 'exhausted'}}} ) {
            console.log( 'notif_servantDieReRolled' );

            const servantDieReRolled = notif['args'];
            console.log( servantDieReRolled );

            if (servantDieReRolled.servantDie) {
                if (servantDieReRolled.exhausted) {
                    this.servantManager.moveServantDieToExhaustedArea(servantDieReRolled.servantDie.id, servantDieReRolled.servantDie.location_arg)
                } else {
                    this.servantManager.moveServantDieToPlayerArea(servantDieReRolled.servantDie.id, servantDieReRolled.servantDie.type, servantDieReRolled.servantDie.location_arg)
                }
            }
        },

        notif_allCardsFlipped: function( notif = {args: {treasureCards: []}}) {
            console.log( 'notif_allCardsFlipped' );

            const allCardsFlipped = notif['args'];
            console.log( allCardsFlipped );

            this.treasureCardManager.renderCardsAndMoveToZone(allCardsFlipped.treasureCards, true);

        }
    });
});
