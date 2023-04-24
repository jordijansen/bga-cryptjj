<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * CryptJj implementation : © Jordi Jansen <thestartplayer@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 */

// Options
const OPTION_COLLECTORS = 100;
const OPTION_COLLECTORS_A = 1;
const OPTION_COLLECTORS_RANDOM = 2;
const OPTION_COLLECTORS_B = 3;

// Collector Types
const COLLECTOR_COLLECT_PHASE = 'COLLECT_PHASE';
const COLLECTOR_END_GAME = 'END_GAME';
const COLLECTOR_ANY_TIME = 'ANY_TIME';
const COLLECTOR_BEFORE_CLAIM_PHASE = 'BEFORE_CLAIM_PHASE';

// Actions
const ACTION_CLAIM_TREASURE = "claimTreasure";
const ACTION_RECOVER_SERVANTS = "recoverServants";
const ACTION_ACTIVATE_COLLECTOR = "activateCollector";
const ACTION_END_TURN = "endTurn";

// States
const STATE_SET_UP = "gameSetup";
const STATE_SET_UP_ID = 1;

const STATE_REVEAL_TREASURE = "revealTreasure";
const STATE_REVEAL_TREASURE_ID = 10;

const STATE_BEFORE_CLAIM_PHASE = "beforeClaimPhase";
const STATE_BEFORE_CLAIM_PHASE_ID = 20;

const STATE_BEFORE_CLAIM_PHASE_ACTIVATE_COLLECTORS = "beforeClaimPhaseActivateCollectors";
const STATE_BEFORE_CLAIM_PHASE_ACTIVATE_COLLECTORS_ID = 21;

const STATE_END_BEFORE_CLAIM_PHASE_ACTIVATE_COLLECTORS = "endBeforeClaimPhaseActivateCollectors";
const STATE_END_BEFORE_CLAIM_PHASE_ACTIVATE_COLLECTORS_ID = 22;

const STATE_BEFORE_PLAYER_TURN = "beforePlayerTurn";
const STATE_BEFORE_PLAYER_TURN_ID = 30;

const STATE_PLAYER_TURN = "playerTurn";
const STATE_PLAYER_TURN_ID = 31;

const STATE_AFTER_PLAYER_TURN = "afterPlayerTurn";
const STATE_AFTER_PLAYER_TURN_ID = 32;

CONST STATE_COLLECT_TREASURE = "collectTreasure";
const STATE_COLLECT_TREASURE_ID = 40;

CONST STATE_PASS_TORCH_CARDS = "passTorchCards";
const STATE_PASS_TORCH_CARDS_ID = 50;

const STATE_GAME_END = "gameEnd";
const STATE_GAME_END_ID = 99;
