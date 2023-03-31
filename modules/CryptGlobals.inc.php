<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * Villagersnew implementation : © Sandra Kuipers sandra@skuipers.com
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 */

// Actions
const ACTION_CLAIM_TREASURE = "claimTreasure";
const ACTION_RECOVER = "recover";

// States
const STATE_SET_UP = "gameSetup";
const STATE_SET_UP_ID = 1;

const STATE_PLAYER_TURN = "playerTurn";
const STATE_PLAYER_TURN_ID = 10;

const STATE_NEXT_PLAYER = "nextPlayer";
const STATE_NEXT_PLAYER_ID = 11;

const STATE_GAME_END = "gameEnd";
const STATE_GAME_END_ID = 99;
