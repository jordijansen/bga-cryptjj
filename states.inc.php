<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Crypt implementation : © Jordi Jansen <thestartplayer@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 * 
 * states.inc.php
 *
 * Crypt game states description
 *
 */

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
        "description" => clienttranslate('${actplayer} may activate collector(s)'),
        "descriptionmyturn" => clienttranslate('${you} may activate collector(s)'),
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
        "transitions" => array( STATE_AFTER_COLLECT_TREASURE => STATE_AFTER_COLLECT_TREASURE_ID )
    ),

    STATE_AFTER_COLLECT_TREASURE_ID => array(
        "name" => STATE_AFTER_COLLECT_TREASURE,
        "description" => '',
        "type" => "game",
        "action" => "stAfterCollectTreasure",
        "updateGameProgression" => false,
        "transitions" => array(STATE_END_AFTER_COLLECT_TREASURE_ACTIVATE_COLLECTORS => STATE_END_AFTER_COLLECT_TREASURE_ACTIVATE_COLLECTORS_ID, STATE_AFTER_COLLECT_TREASURE_ACTIVATE_COLLECTORS => STATE_AFTER_COLLECT_TREASURE_ACTIVATE_COLLECTORS_ID)
    ),

    STATE_AFTER_COLLECT_TREASURE_ACTIVATE_COLLECTORS_ID => array(
        "name" => STATE_AFTER_COLLECT_TREASURE_ACTIVATE_COLLECTORS,
        "description" => clienttranslate('${actplayer} may activate collector(s)'),
        "descriptionmyturn" => clienttranslate('${you} may activate collector(s)'),
        "args" => "argStateAfterCollectTreasureActivateCollectors",
        "type" => "activeplayer",
        "updateGameProgression" => false,
        "possibleactions" => array( ACTION_ACTIVATE_COLLECTOR, ACTION_END_TURN ),
        "transitions" => array( STATE_END_AFTER_COLLECT_TREASURE_ACTIVATE_COLLECTORS => STATE_END_AFTER_COLLECT_TREASURE_ACTIVATE_COLLECTORS_ID )
    ),

    STATE_END_AFTER_COLLECT_TREASURE_ACTIVATE_COLLECTORS_ID => array(
        "name" => STATE_END_AFTER_COLLECT_TREASURE_ACTIVATE_COLLECTORS,
        "description" => '',
        "type" => "game",
        "action" => "stEndAfterCollectTreasureActivateCollectors",
        "updateGameProgression" => false,
        "transitions" => array(STATE_AFTER_COLLECT_TREASURE => STATE_AFTER_COLLECT_TREASURE_ID, STATE_PASS_TORCH_CARDS => STATE_PASS_TORCH_CARDS_ID, STATE_BEFORE_GAME_END => STATE_BEFORE_GAME_END_ID)
    ),

    STATE_PASS_TORCH_CARDS_ID => array(
        "name" => STATE_PASS_TORCH_CARDS,
        "description" => clienttranslate('Pass the Torch: passing torch cards...'),
        "type" => "game",
        "action" => "stPassTorchCards",
        "updateGameProgression" => false,
        "transitions" => array( STATE_REVEAL_TREASURE => STATE_REVEAL_TREASURE_ID )
    ),

    STATE_BEFORE_GAME_END_ID => array(
        "name" => STATE_BEFORE_GAME_END,
        "description" => clienttranslate('Final Scoring'),
        "type" => "game",
        "action" => "stBeforeGameEnd",
        "updateGameProgression" => false,
        "transitions" => array( STATE_GAME_END => STATE_GAME_END_ID )
    ),
   
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
