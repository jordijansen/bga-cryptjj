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

        g_gamethemeurl + 'modules/js/oversurface.js',
        g_gamethemeurl + 'modules/js/ActionManager.js',
        g_gamethemeurl + 'modules/js/DeckManager.js',
        g_gamethemeurl + 'modules/js/DisplayManager.js',
        g_gamethemeurl + 'modules/js/PlayerManager.js',
        g_gamethemeurl + 'modules/js/ServantManager.js',
    ],
function (dojo, declare) {
    return declare("bgagame.cryptjj", ebg.core.gamegui, {
        constructor: function(){
            console.log('cryptjj constructor');

            this.cardWidth = 178;
            this.cardHeight = 261;

            this.actionManager = new crypt.ActionManager(this);
            this.deckManager = new crypt.DeckManager(this);
            this.displayManager = new crypt.DisplayManager(this);
            this.playerManager = new crypt.PlayerManager(this);
            this.servantManager = new crypt.ServantManager(this);
            // Here, you can init the global variables of your user interface
            // Example:
            // this.myGlobalValue = 0;

            this.gameStates = {
                playerTurn: 'playerTurn',
                claimTreasure: 'claimTreasure' // Client Side only state
            }

            this.gameActions = {
                claimTreasure: 'claimTreasure'
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
        
        setup: function( gameData )
        {
            console.log( "Starting game setup" );
            console.dir(gameData);

            this.deckManager.setup(gameData);
            this.displayManager.setup(gameData);
            this.playerManager.setup(gameData);
            this.servantManager.setup(gameData);
 
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            console.log( "Ending game setup" );
        },
       

        ///////////////////////////////////////////////////
        //// Game & client states
        
        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            console.log( 'Entering state: '+stateName );
            
            switch( stateName )
            {
            
            /* Example:
            
            case 'myGameState':
            
                // Show some HTML block at this game state
                dojo.style( 'my_html_block_id', 'display', 'block' );
                
                break;
           */
           
           
            case 'dummmy':
                break;
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            console.log( 'Leaving state: '+stateName );
            
            switch( stateName )
            {
            
            /* Example:
            
            case 'myGameState':
            
                // Hide the HTML block we are displaying only during this game state
                dojo.style( 'my_html_block_id', 'display', 'none' );
                
                break;
           */
           
           
            case 'dummmy':
                break;
            }               
        }, 

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function( stateName, args )
        {
            console.log( 'onUpdateActionButtons: '+stateName );
                      
            if( this.isCurrentPlayerActive() )
            {            
                switch( stateName )
                {
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
                        this.addActionButton( 'claim_treasure_button', _('Claim Treasure'), 'enterClaimTreasureMode' );
                        break;
                    case this.gameStates.claimTreasure:
                        this.addActionButton( 'confirm_claim_treasure_state', _('Confirm'), 'confirmClaimTreasure');
                        this.addActionButton( 'undo_claim_treasure_state', _('Undo'), 'undoClaimTreasure');
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

        enterClaimTreasureMode: function( evt )
        {
            console.log( 'enterClaimTreasureMode' );

            // Preventing default browser reaction
            dojo.stopEvent( evt );

            if( ! this.checkAction( this.gameActions.claimTreasure ) )
            {   return; }

            if (this.servantManager.getServantDieInPlayerArea(this.player_id).length > 0) {
                this.setClientState(this.gameStates.claimTreasure, {
                    descriptionmyturn: _("${you} must claim treasure using your servants and choose their effort value")
                })

                this.displayManager.enterClaimTreasureMode(false);
            } else {
                this.showMessage( _('You have no servant dice available, recover servants first'), 'error');
            }
        },


        undoClaimTreasure: function( evt )
        {
            console.log( 'undoClaimTreasure' );

            // Preventing default browser reaction
            dojo.stopEvent( evt );

            this.displayManager.exitClaimTreasureMode(true);
            this.restoreServerGameState();
        },

        confirmClaimTreasure: function( evt )
        {
            console.log( 'confirmClaimTreasure' );

            // Preventing default browser reaction
            dojo.stopEvent( evt );

            if (this.displayManager.isCurrentSelectionValid()) {
                this.actionManager.claimTreasure(this.displayManager.getCurrentSelection())
                this.displayManager.exitClaimTreasureMode();
            } else {
                this.showMessage( _('Servant(s) effort too low'), 'error');
            }
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
            
            // TODO: here, associate your game notifications with local methods

            dojo.subscribe('treasureCardClaimed', this, 'notif_treasureCardClaimed');
            dojo.subscribe('treasureCardBumped', this, 'notif_treasureCardBumped');


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

            Object.values(treasureCardBumped.bumpedServantDice).forEach(servantDie => this.servantManager.moveServantDieToExhaustedArea(servantDie.id, servantDie.location_arg))
        },
    });
});
