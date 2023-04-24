<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * CryptJj implementation : © Jordi Jansen <thestartplayer@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 * 
 * states.inc.php
 *
 * CryptJj game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!

 
$machinestates = array(

    // The initial state. Please do not modify.
    STATE_SET_UP_ID => array(
        "name" => STATE_SET_UP,
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array( "" => STATE_REVEAL_TREASURE_ID )
    ),

    STATE_REVEAL_TREASURE_ID => array(
        "name" => STATE_REVEAL_TREASURE,
        "description" => clienttranslate('Reveal: revealing treasure cards...'),
        "type" => "game",
        "action" => "stRevealTreasure",
        "updateGameProgression" => false,
        "transitions" => array( STATE_BEFORE_CLAIM_PHASE => STATE_BEFORE_CLAIM_PHASE_ID)
    ),

    STATE_BEFORE_CLAIM_PHASE_ID => array(
        "name" => STATE_BEFORE_CLAIM_PHASE,
        "description" => '',
        "type" => "game",
        "action" => "stBeforeClaimPhase",
        "updateGameProgression" => false,
        "transitions" => array(STATE_END_BEFORE_CLAIM_PHASE_ACTIVATE_COLLECTORS => STATE_END_BEFORE_CLAIM_PHASE_ACTIVATE_COLLECTORS_ID, STATE_BEFORE_CLAIM_PHASE_ACTIVATE_COLLECTORS => STATE_BEFORE_CLAIM_PHASE_ACTIVATE_COLLECTORS_ID)
    ),

    STATE_BEFORE_CLAIM_PHASE_ACTIVATE_COLLECTORS_ID => array(
        "name" => STATE_BEFORE_CLAIM_PHASE_ACTIVATE_COLLECTORS,
        "description" => clienttranslate('${actplayer} may activate collectors'),
        "descriptionmyturn" => clienttranslate('${you} may activate collectors'),
        "type" => "activeplayer",
        "updateGameProgression" => false,
        "possibleactions" => array( ACTION_ACTIVATE_COLLECTOR, ACTION_END_TURN ),
        "transitions" => array( STATE_END_BEFORE_CLAIM_PHASE_ACTIVATE_COLLECTORS => STATE_END_BEFORE_CLAIM_PHASE_ACTIVATE_COLLECTORS_ID )
    ),

    STATE_END_BEFORE_CLAIM_PHASE_ACTIVATE_COLLECTORS_ID => array(
        "name" => STATE_END_BEFORE_CLAIM_PHASE_ACTIVATE_COLLECTORS,
        "description" => '',
        "type" => "game",
        "action" => "stEndBeforeClaimPhaseActivateCollectors",
        "updateGameProgression" => false,
        "transitions" => array(STATE_BEFORE_CLAIM_PHASE => STATE_BEFORE_CLAIM_PHASE_ID, STATE_BEFORE_PLAYER_TURN => STATE_BEFORE_PLAYER_TURN_ID)
    ),

    STATE_BEFORE_PLAYER_TURN_ID => array(
        "name" => STATE_BEFORE_PLAYER_TURN,
        "description" => '',
        "type" => "game",
        "action" => "stBeforePlayerTurn",
        "updateGameProgression" => false,
        "transitions" => array(STATE_PLAYER_TURN => STATE_PLAYER_TURN_ID, STATE_AFTER_PLAYER_TURN => STATE_AFTER_PLAYER_TURN_ID)
    ),

    STATE_PLAYER_TURN_ID => array(
        "name" => STATE_PLAYER_TURN,
        "description" => clienttranslate('Claim: ${actplayer} must claim treasure card(s) or recover servant dice'),
        "descriptionmyturn" => clienttranslate('Claim: ${you} must claim treasure card(s) or recover servant dice'),
        "args" => "argStatePlayerTurn",
        "type" => "activeplayer",
        "possibleactions" => array( ACTION_CLAIM_TREASURE, ACTION_RECOVER_SERVANTS ),
        "transitions" => array( STATE_AFTER_PLAYER_TURN => STATE_AFTER_PLAYER_TURN_ID )
    ),

    STATE_AFTER_PLAYER_TURN_ID => array(
        "name" => STATE_AFTER_PLAYER_TURN,
        "description" => '',
        "type" => "game",
        "action" => "stAfterPlayerTurn",
        "updateGameProgression" => false,
        "transitions" => array(STATE_BEFORE_PLAYER_TURN => STATE_BEFORE_PLAYER_TURN_ID, STATE_COLLECT_TREASURE => STATE_COLLECT_TREASURE_ID)
    ),

    STATE_COLLECT_TREASURE_ID => array(
        "name" => STATE_COLLECT_TREASURE,
        "description" => clienttranslate('Collect: distributing treasure card(s) to players...'),
        "type" => "game",
        "action" => "stCollectTreasure",
        "updateGameProgression" => false,
        "transitions" => array( STATE_PASS_TORCH_CARDS => STATE_PASS_TORCH_CARDS_ID, STATE_GAME_END => STATE_GAME_END_ID, )
    ),

    STATE_PASS_TORCH_CARDS_ID => array(
        "name" => STATE_PASS_TORCH_CARDS,
        "description" => 'Pass the Torch: passing torch cards...',
        "type" => "game",
        "action" => "stPassTorchCards",
        "updateGameProgression" => false,
        "transitions" => array( STATE_REVEAL_TREASURE => STATE_REVEAL_TREASURE_ID )
    ),

/*
    Examples:
    
    2 => array(
        "name" => "nextPlayer",
        "description" => '',
        "type" => "game",
        "action" => "stNextPlayer",
        "updateGameProgression" => true,   
        "transitions" => array( "endGame" => 99, "nextPlayer" => 10 )
    ),
    
    10 => array(
        "name" => "playerTurn",
        "description" => clienttranslate('${actplayer} must play a card or pass'),
        "descriptionmyturn" => clienttranslate('${you} must play a card or pass'),
        "type" => "activeplayer",
        "possibleactions" => array( "playCard", "pass" ),
        "transitions" => array( "playCard" => 2, "pass" => 2 )
    ), 

*/    
   
    // Final state.
    // Please do not modify (and do not overload action/args methods).
    STATE_GAME_END_ID => array(
        "name" => STATE_GAME_END,
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )

);



