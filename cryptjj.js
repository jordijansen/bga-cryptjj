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

        g_gamethemeurl + 'modules/js/DeckManager.js',
        g_gamethemeurl + 'modules/js/DisplayManager.js',
    ],
function (dojo, declare) {
    return declare("bgagame.cryptjj", ebg.core.gamegui, {
        constructor: function(){
            console.log('cryptjj constructor');

            this.deckManager = new crypt.DeckManager(this);
            this.displayManager = new crypt.DisplayManager(this);
            // Here, you can init the global variables of your user interface
            // Example:
            // this.myGlobalValue = 0;

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
        
        setup: function( gamedatas )
        {
            console.log( "Starting game setup" );
            console.dir(gamedatas);

            // Set-up treasure deck and display
            this.deckManager.setup(gamedatas);
            this.displayManager.setup(gamedatas);

            // Set-up player areas
            let thisPlayerArea;
            const otherPlayerAreas = [];
            for( var player_id in gamedatas.players )
            {
                var player = gamedatas.players[player_id];
                var playerArea = this.format_block('jstpl_player_area', {
                    "id": player.id,
                    "color" : player.color,
                    "name": player.name
                });
                if (Number(player.id) === this.player_id) {
                    thisPlayerArea = playerArea;
                } else {
                    otherPlayerAreas.push(playerArea);
                }
            }
            dojo.place(thisPlayerArea, "player-areas-row")
            otherPlayerAreas.forEach(playerArea => dojo.place(playerArea, "player-areas-row"))

            // Set-up servant dice
            for( var playerId in gamedatas.servant_dice )
            {
                var servantDiceForPlayer = gamedatas.servant_dice[playerId];
                for (var index in servantDiceForPlayer) {
                    var servantDie = servantDiceForPlayer[index];
                    if (servantDie.location === 'player_area') {
                        var servantDieElement = this.format_block('jstpl_die_' + servantDie.location_arg, {
                            "id": servantDie.id,
                            "type" : servantDie.type,
                            "color": servantDie.type_arg
                        });
                        dojo.place(servantDieElement, "player-area-die-area-" + servantDie.type)
                    } else if (servantDie.location === 'treasure_card') {
                        // TODO implement placement on card
                    } else if (servantDie.location === 'exhausted') {
                        // TODO implement placement on exhausted
                    }
                }
            }
 
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
                    case 'playerTurn':
                        this.addActionButton( 'claim_treasure_button', _('Claim Treasure'), 'enterClaimTreasureMode' );
                        this.addActionButton( 'recover_servant_dice_button', _('Recover Servants'), 'onMyMethodToCall2' );
                        break;
                    case 'claimTreasureState':
                        this.addActionButton( 'undo_claim_treasure_state', _('Undo'), 'undoClaimTreasureState');
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

            if( ! this.checkAction( 'claimTreasure' ) )
            {   return; }

            this.setClientState('claimTreasureState')

            for (var i in this.gamedatas.treasure_cards_display) {
                const card_id = this.gamedatas.treasure_cards_display[i].id;
                dojo.toggleClass($('treasure-card-' + card_id), 'selectable')
            }
        },

        onTreasureCardClick: function(evt)
        {
            console.log('click');
        },

        undoClaimTreasureState: function( evt )
        {
            console.log( 'undoClaimTreasureState' );

            // Preventing default browser reaction
            dojo.stopEvent( evt );

            if(this.on_client_state) {
                for (var i in this.gamedatas.treasure_cards_display) {
                    const card_id = this.gamedatas.treasure_cards_display[i].id;
                    dojo.toggleClass($('treasure-card-' + card_id), 'selectable')
                }

                this.disconnectAll();

                this.restoreServerGameState();
            }
        },

        onMyMethodToCall2: function( evt )
        {
            console.log( 'onMyMethodToCall2' );

            // Preventing default browser reaction
            dojo.stopEvent( evt );
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
   });             
});
