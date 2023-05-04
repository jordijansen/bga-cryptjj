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
  * cryptjj.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );

require_once("modules/CryptGlobals.inc.php");
require_once("modules/CryptTreasureCards.inc.php");
require_once("modules/CryptServantDice.inc.php");
require_once("modules/CryptPlayerManager.inc.php");
require_once("modules/CryptNotifications.inc.php");
require_once("modules/CryptCollectorCards.inc.php");
require_once("modules/CryptScoreManager.inc.php");

class CryptJj extends Table
{
	function __construct( )
	{
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();
        
        self::initGameStateLabels( array(
            GLOBAL_IDOL_B_USED => 10
        ) );

        $this->treasure_cards = self::getNew( "module.common.deck" );
        $this->treasure_cards->init( "treasure_cards" );

        $this->servant_dice = self::getNew( "module.common.deck" );
        $this->servant_dice->init( "servant_dice" );

        $this->treasureCardsManager = new CryptTreasureCards($this);
        $this->servantDiceManager = new CryptServantDice($this);
        $this->playerManager = new CryptPlayerManager($this);
        $this->notificationsManager = new CryptNotifications($this);
        $this->collectorCardsManager = new CryptCollectorCards($this);
        $this->scoreManager = new CryptScoreManager($this);
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "cryptjj";
    }	

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
    {    
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar, player_card_side) VALUES ";
        $values = array();
        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."',".bga_rand(1, 2).")";
        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );
        self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();
        
        /************ Start the game initialization *****/

        // Init global values with their initial values
        self::setGameStateInitialValue( GLOBAL_IDOL_B_USED, 0 );

        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        include("stats.inc.php");
        foreach ($stats_type as $type => $stats) { // type = "table"/"player"
            foreach ($stats as $stat => $statDetails) {
                self::initStat($type, $stat, 0);
            }
        }

        // 1. Create Collector Cards
        $this->collectorCardsManager->createCollectorCards($options);

        // 2. Create Treasure Deck
        $this->treasureCardsManager->createInitialTreasureCardsDeck($players);

        // 3. Create Servant Dice and place in player_area
        $this->servantDiceManager->createServantDice();

        // 4. Distribute torch & last light cards to players
        $this->playerManager->distributeInitialTorchCards($players);

        // 5. Set initial scores
        $this->scoreManager->setInitialScore($players);

        /************ End of the game initialization *****/
    }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array();

        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!

        $result['collectors'] = $this->collectorCardsManager->getCollectors();
        $result['players'] = $this->playerManager->getPlayersInTurnOrder();

        $players = self::loadPlayersBasicInfos();
        $result['servantDice'] = array();
        foreach( $players as $playerId => $player )
        {
            foreach ($this->servantDiceManager->getAllServantDice($playerId) as $servantDie) {
                $result['servantDice'][] = $servantDie;
            }
        }
        $result['treasureDeck']['size'] = $this->treasureCardsManager->countCardsInDeck();
        $topCardOfDeck = $this->treasure_cards->getCardOnTop('deck');
        $result['treasureDeck']['topCardType'] = isset($topCardOfDeck) ? $topCardOfDeck['type'] : 'empty';
        $result['treasureCards'] = $this->treasureCardsManager->getAllTreasureCardsInPlay($current_player_id);


        // TODO: Gather all information about current game situation (visible by player $current_player_id).
  
        return $result;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
        $playerCount = $this->getPlayerCount();
        $totalNumberOfCardsInDisplayEachRound = $this->treasureCardsManager->determineNumberOfFaceUpTreasureCards($playerCount) + $this->treasureCardsManager->determineNumberOfFaceDownTreasureCards($playerCount);

        $totalNumberOfRounds = $this->treasureCardsManager->countCards() / $totalNumberOfCardsInDisplayEachRound;
        $numberOfRoundsLeft = $this->treasureCardsManager->countCardsInDeck() / $totalNumberOfCardsInDisplayEachRound;

        $percentageLeft = ($numberOfRoundsLeft / $totalNumberOfRounds) * 100;
        return 100 - $percentageLeft;
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */

    public function findById($array, $id) {
        foreach ($array as $item) {
            if ($item['id'] == $id) {
                return $item;
            }
        }
        return null;
    }
 
    public function getPlayer($playerId) // used by CryptNotifications
    {
        $players = self::loadPlayersBasicInfos();

        foreach ($players as $id => $player) {
            if ($id == $playerId) {
                return $player;
            }
        }
        return null;
    }

    public function getPlayerCount() {
        $players = self::loadPlayersBasicInfos();
        return sizeof($players);
    }

    public function getStateName() {
        $state = $this->gamestate->state();
        return $state['name'];
    }

    /**
     * DEBUG FUNCTIONS!!
     */

    public function runDebug() {

        $this->scoreManager->breakTies();
    }

    public function runIntermediateScoring() {
        $players = $this->loadPlayersBasicInfos();
        foreach($players as $playerId => $player)
        {
            // Update the scores for all players
            $this->scoreManager->updateTotalScore($playerId, true);
        }

        $finalScoring = [];
        foreach( $players as $playerId => $player )
        {
            $finalScoring[$playerId] = $this->scoreManager->getScoreBreakDown($playerId);
        }
        self::trace(json_encode($finalScoring));

        $this->notificationsManager->notifyFinalScoring($finalScoring);
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in cryptjj.action.php)
    */

    /*
    
    Example:

    function playCard( $card_id )
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
        self::checkAction( 'playCard' ); 
        
        $player_id = self::getActivePlayerId();
        
        // Add your game logic to play a card there 
        ...
        
        // Notify all players about the card played
        self::notifyAllPlayers( "cardPlayed", clienttranslate( '${player_name} plays ${card_name}' ), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'card_name' => $card_name,
            'card_id' => $card_id
        ) );
          
    }
    
    */

    /**
     * @throws BgaUserException
     */
    function claimTreasure($claimTreasureSelection )
    {
        // Check if this is a valid action
        self::checkAction(ACTION_CLAIM_TREASURE);

        self::trace(json_encode($claimTreasureSelection));

        $cardsInDisplay = $this->treasureCardsManager->getTreasureCardsInDisplayForPlayer(self::getActivePlayerId());

        self::trace(json_encode($cardsInDisplay));

        if (sizeof($claimTreasureSelection) === 0) {
            throw new BgaUserException(clienttranslate("You need to claim at least one treasure card"));
        }

        if (sizeof($claimTreasureSelection) > 1) {
            if ($this->playerManager->hasBothCards(self::getActivePlayerId())) {
                if (self::getUniqueValueFromDB("SELECT has_played_before_this_round FROM player WHERE player_id = " .$this->getActivePlayerId()) == 1) {
                    throw new BgaUserException(clienttranslate("You can only claim one card if you have the Lights Out card"));
                }
            } else if ($this->playerManager->hasLightsOutCard(self::getActivePlayerId())) {
                throw new BgaUserException(clienttranslate("You can only claim one card if you have the Lights Out card"));
            }
        }

        foreach($claimTreasureSelection as $treasureCardSelection)
        {
            if (sizeof($treasureCardSelection['servantDice']) > 0) {
                $servantDice = $this->servantDiceManager->getServantDiceInPlayerArea(self::getActivePlayerId());
                self::trace(json_encode($servantDice));

                // Check if the provided treasure card is in the treasure card display
                $treasureCard = self::findById($cardsInDisplay, $treasureCardSelection['id']);
                if ($treasureCard == null) {
                    throw new BgaUserException(clienttranslate("Card not in treasure card display!"));
                }

                // Check if the provided servant dice are in the player area
                foreach ($treasureCardSelection['servantDice'] as $servantDieId) {
                    if ($servantDice[$servantDieId] == null) {
                        throw new BgaUserException(clienttranslate("Servant die not owned by active player!"));
                    }
                }

                // Check if there are already servantDice on the treasure card from other players
                $servantDiceOnTreasureCard = $this->servantDiceManager->getServantDiceOnTreasureCard($treasureCardSelection['id']);
                if (sizeof($servantDiceOnTreasureCard) > 0) {
                    $treasureCardSelectionEffort = sizeof($treasureCardSelection['servantDice']) * $treasureCardSelection['value'];
                    $treasureCardEffort = array_sum(array_column($servantDiceOnTreasureCard, 'location_arg'));
                    // The effort value needs to be higher
                    if ($treasureCardEffort >= $treasureCardSelectionEffort) {
                        throw new BgaUserException(clienttranslate("Servant(s) effort too low"));
                    }
                    // Bump the other servants of the card
                    $this->servantDiceManager->recoverServantDice(array_column($servantDiceOnTreasureCard, 'id'));
                    $this->notificationsManager->notifyTreasureCardBumped(self::getActivePlayerId(), $treasureCard['id'], $servantDiceOnTreasureCard);
                }

                // Move the servant dice to the treasure cards and update their value
                $servantDice = array();
                foreach ($treasureCardSelection['servantDice'] as $servantDieId) {
                    $servantDice[] = $this->servantDiceManager->moveServantDiceToTreasureCardWithValue($servantDieId, $treasureCardSelection['id'], $treasureCardSelection['value']);
                }

                $this->notificationsManager->notifyTreasureCardClaimed(self::getActivePlayerId(), $treasureCard['id'], $servantDice);
            }
        }

        self::incStat(1, STAT_PLAYER_CLAIM_TREASURE_ACTION_COUNT, self::getActivePlayerId());

        $this->gamestate->nextState(STATE_AFTER_PLAYER_TURN);
    }

    function recoverServants($isPlayerInitiated) {
        // Check if this is a valid action
        if ($isPlayerInitiated) {
            self::checkAction(ACTION_RECOVER_SERVANTS);
        }

        $exhaustedServantDice = $this->servantDiceManager->getServantDiceInExhaustedArea(self::getActivePlayerId());
        $this->servantDiceManager->recoverServantDice(array_column($exhaustedServantDice, 'id'));
        $this->notificationsManager->notifyServantDiceRecovered(self::getActivePlayerId(), $exhaustedServantDice, $isPlayerInitiated);

        self::incStat(1, STAT_PLAYER_RECOVER_SERVANTS_ACTION_COUNT, self::getActivePlayerId());

        $this->gamestate->nextState(STATE_AFTER_PLAYER_TURN);
    }

    function activateCollector($activateCollector)
    {
        self::trace(json_encode($activateCollector));

        $collector = $this->collectorCardsManager->getCollector($activateCollector['collectorId']);
        $treasureCardsToFlip = [];
        foreach ($activateCollector['treasureCardsToFlip'] as $treasureCardId) {
            $treasureCardsToFlip[] = $this->treasureCardsManager->getTreasureCard($treasureCardId, self::getCurrentPlayerId());
        }

        if (isset($collector)) {
            if ($this->isCollectorAllowed($collector)) {
                if (sizeof($treasureCardsToFlip) == $collector['nr_of_cards_to_flip']) {
                    $this->collectorCardsManager->useCollector(self::getCurrentPlayerId(), $collector, $treasureCardsToFlip, $activateCollector['treasureCardsSelected'], $activateCollector['servantDiceSelected']);
                } else {
                    throw new BgaUserException(clienttranslate("Wrong number of cards provided"));
                }
            } else {
                throw new BgaUserException(clienttranslate("Collector not allowed at the moment"));
            }
        } else {
            throw new BgaUserException(clienttranslate("Unknown collector!"));
        }

    }

    function endTurn($checkAction = true) {
        if ($checkAction) {
            self::checkAction(ACTION_END_TURN);
        }

        if ($this->getStateName() === STATE_BEFORE_CLAIM_PHASE_ACTIVATE_COLLECTORS) {
            $this->gamestate->nextState(STATE_END_BEFORE_CLAIM_PHASE_ACTIVATE_COLLECTORS);
        } else if ($this->getStateName() === STATE_AFTER_COLLECT_TREASURE_ACTIVATE_COLLECTORS) {
            $this->gamestate->nextState(STATE_END_AFTER_COLLECT_TREASURE_ACTIVATE_COLLECTORS);
        } else if ($this->getStateName() === STATE_PLAYER_TURN) {
            $this->gamestate->nextState(STATE_AFTER_PLAYER_TURN);
        }
    }

    function isCollectorAllowed($collector) {
        if ($collector['ability_type'] === 'ANY_TIME') {
            return true;
        } else if ($collector['ability_type'] === COLLECTOR_BEFORE_CLAIM_PHASE
            && $this->getStateName() === STATE_BEFORE_CLAIM_PHASE_ACTIVATE_COLLECTORS
            && $this->getActivePlayerId() === self::getCurrentPlayerId()) {
            return true;
        } else if ($collector['ability_type'] === COLLECTOR_COLLECT_PHASE
            && $this->getStateName() === STATE_AFTER_COLLECT_TREASURE_ACTIVATE_COLLECTORS
            && $this->getActivePlayerId() === self::getCurrentPlayerId()) {
            return true;
        }
        return false;
    }

    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    /*
    
    Example for game state "MyGameState":

    function argMyGameState()
    {
        // Get some values from the current game situation in database...
    
        // return values:
        return array(
            'variable1' => $value1,
            'variable2' => $value2,
            ...
        );
    }    
    */

    // TODO PROVIDE ARGS TO BEFORE CLAIM PHASE TURN
    function argStatePlayerTurn()
    {
        return array(
          'playedBeforeThisRound' => self::getUniqueValueFromDB("SELECT has_played_before_this_round FROM player WHERE player_id = " .$this->getActivePlayerId()) == 1,
          'usedManuscriptBThisRound' => self::getUniqueValueFromDB("SELECT has_used_manuscript_b_this_round FROM player WHERE player_id = " .$this->getActivePlayerId()) == 1
        );
    }

    function argStateAfterCollectTreasureActivateCollectors() {
        return array(
          'servantDiceForReRoll' => $this->servantDiceManager->getServantDiceForReRoll($this->getActivePlayerId())
        );
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */
    
    /*
    
    Example for game state "MyGameState":

    function stMyGameState()
    {
        // Do some stuff ...
        
        // (very often) go to another gamestate
        $this->gamestate->nextState( 'some_gamestate_transition' );
    }    
    */

    function stRevealTreasure() {
        self::debug("stRevealTreasure");
        self::incStat(1, STAT_TABLE_NUMBER_OF_ROUNDS_PLAYED);

        $this->treasureCardsManager->drawTreasureCardsForDisplay($this->getPlayerCount());

        $treasureDeck = [];
        $treasureDeck['size'] = $this->treasureCardsManager->countCardsInDeck();
        $topCardOfDeck = $this->treasure_cards->getCardOnTop('deck');
        $treasureDeck['topCardType'] = isset($topCardOfDeck) ? $topCardOfDeck['type'] : 'empty';

        self::notifyAllPlayers( 'treasureCardDisplayUpdated', clienttranslate( 'Treasure card display filled with treasure cards'), array(
            'treasureCards' => $this->treasureCardsManager->getAllTreasureCardsInDisplay(),
            'treasureDeck' => $treasureDeck
        ));

        $this->gamestate->changeActivePlayer($this->playerManager->getLeaderPlayerId());
        $this->gamestate->nextState(STATE_BEFORE_CLAIM_PHASE);
    }

    function stBeforeClaimPhase() {
        $collectorsThatCanBeUsedByPlayer = $this->collectorCardsManager->getAvailableCollectors($this->getActivePlayerId(), COLLECTOR_BEFORE_CLAIM_PHASE);
        if (sizeof($collectorsThatCanBeUsedByPlayer) > 0) {
            self::giveExtraTime($this->getActivePlayerId());
            $this->gamestate->nextState(STATE_BEFORE_CLAIM_PHASE_ACTIVATE_COLLECTORS);
        } else {
            $this->gamestate->nextState(STATE_END_BEFORE_CLAIM_PHASE_ACTIVATE_COLLECTORS);
        }
    }

    function stEndBeforeClaimPhaseActivateCollectors() {
        $playersCustomOrderNo = $this->playerManager->getPlayerCustomOrderNo($this->getActivePlayerId());
        // If the player now has two Idol B cards, they may flip them to score extra during END_GAME scoring
        $this->collectorCardsManager->activateIdolBIfInPlay();

        if ($playersCustomOrderNo == $this->getPlayerCount()) {
            // This was the last player, so we activate the leader player again and move to the Claim Treasure phase
            $this->gamestate->changeActivePlayer($this->playerManager->getLeaderPlayerId());
            $this->gamestate->nextState(STATE_BEFORE_PLAYER_TURN);
        } else {
            // This was not the last player, so we go to the next player and see if they have any collectors to activate.
            $this->activeNextPlayer();
            $this->gamestate->nextState(STATE_BEFORE_CLAIM_PHASE);
        }
    }

    function stBeforePlayerTurn() {
        self::debug("stBeforePlayerTurn");
        if (sizeof($this->servantDiceManager->getServantDiceInPlayerArea($this->getActivePlayerId())) > 0) {
            self::giveExtraTime($this->getActivePlayerId());
            $this->gamestate->nextState(STATE_PLAYER_TURN);
        } else {
            $collector = $this->collectorCardsManager->getAvailableCollectorById($this->getActivePlayerId(), 'remains-A');
            if (isset($collector)) {
                self::giveExtraTime($this->getActivePlayerId());
                $this->gamestate->nextState(STATE_PLAYER_TURN);
            } else {
                // If the player has no servant dice in their player area we automatically perform the recover servants action, and they can't recover using remains-A
                $this->recoverServants(false);
            }
        }
    }

    function stAfterPlayerTurn() {
        self::debug("stAfterPlayerTurn");
        // Two Player Only, one player has both cards
        if ($this->playerManager->hasLightsOutCard($this->getActivePlayerId()) && $this->playerManager->hasLeaderCard($this->getActivePlayerId())) {
            if (self::getUniqueValueFromDB("SELECT has_played_before_this_round FROM player WHERE player_id = " .$this->getActivePlayerId()) == 1) {
                $this->gamestate->nextState(STATE_COLLECT_TREASURE);
            } else {
                self::DbQuery("UPDATE player SET has_played_before_this_round=1 WHERE player_id = " .$this->getActivePlayerId());
                $this->activeNextPlayer();
                $this->gamestate->nextState(STATE_BEFORE_PLAYER_TURN);
            }
        } else {
            // If the current player has the LightsOutCard we move into the Collect Treasure State
            if ($this->playerManager->hasLightsOutCard($this->getActivePlayerId())) {
                $this->gamestate->nextState(STATE_COLLECT_TREASURE);
            } else {
                $this->activeNextPlayer();
                $this->gamestate->nextState(STATE_BEFORE_PLAYER_TURN);
            }
        }

    }

    function stCollectTreasure() {
        self::debug("stCollectTreasure");

        // 1. If a player has no servants on Treasure Cards, reclaim all servants
        $players = $this->loadPlayersBasicInfos();
        foreach( $players as $playerId => $player )
        {
            $servantDiceOnTreasureCard = $this->servantDiceManager->getServantDiceOnTreasureCards($playerId);
            $exhaustedServantDiceForPlayer = $this->servantDiceManager->getServantDiceInExhaustedArea($playerId);
            if (sizeof($servantDiceOnTreasureCard) === 0 && sizeof($exhaustedServantDiceForPlayer) > 0) {
                $this->servantDiceManager->recoverServantDice(array_column($exhaustedServantDiceForPlayer, 'id'));
                $this->notificationsManager->notifyServantDiceRecovered($playerId, $exhaustedServantDiceForPlayer, true);
            }
        }

        // 2. For Each Servant Card
        $treasureCardsInDisplay = $this->treasureCardsManager->getAllTreasureCardsInDisplay();
        foreach ($treasureCardsInDisplay as $treasureCard) {
            $servantDiceOnTreasureCard = $this->servantDiceManager->getServantDiceOnTreasureCard($treasureCard['id']);
            // 2.1 if No Servants on Card, discard card
            if (sizeof($servantDiceOnTreasureCard) === 0) {
                $this->treasureCardsManager->discardTreasureCard($treasureCard['id']);
                $this->notificationsManager->notifyTreasureCardDiscarded($treasureCard['id']);
            } else {
                // 2.2 if Servants on Card, Roll each Servant.
                // If less than effort  > exhaust.
                // If equal or higher than effort > return to player
                $playerId = null;
                $rolledServantDice = array();

                $effort = reset($servantDiceOnTreasureCard)['location_arg'];
                foreach ($servantDiceOnTreasureCard as $servantDie) {
                    $playerId = $servantDie['type'];
                    $rolledValue = bga_rand(1, 6);
                    self::debug($servantDie['id'] .' => '. $rolledValue);
                    if ($rolledValue < $effort) {
                        $this->servantDiceManager->exhaustServantDie($servantDie['id']);
                    } else {
                        $this->servantDiceManager->recoverServantDice(array($servantDie['id']));
                    }
                    $this->servantDiceManager->setDieValue($servantDie['id'], $rolledValue);

                    $rolledServantDice[] = $this->servantDiceManager->getServantDie($servantDie['id']);
                }

                $this->treasureCardsManager->collectTreasureCard($playerId, $treasureCard['id']);
                $this->notificationsManager->notifyTreasureCardCollected($playerId, $treasureCard['id'], $effort, $rolledServantDice);
            }
        }

        // The players that now have 2 idol cards may flip them face-up to score extra during END_GAME scoring
        $this->collectorCardsManager->activateIdolBIfInPlay();

        $this->gamestate->changeActivePlayer($this->playerManager->getLeaderPlayerId());
        $this->gamestate->nextState(STATE_AFTER_COLLECT_TREASURE);
    }

    function stAfterCollectTreasure() {
        $collectorsThatCanBeUsedByPlayer = $this->collectorCardsManager->getAvailableCollectors($this->getActivePlayerId(), COLLECTOR_COLLECT_PHASE);
        if (sizeof($collectorsThatCanBeUsedByPlayer) > 0 && sizeof($this->servantDiceManager->getServantDiceInExhaustedAreaWithEffortValue($this->getActivePlayerId())) > 0) {
            // Only activate this state for the player if they have dice that can be re-rolled
            self::giveExtraTime($this->getActivePlayerId());
            $this->gamestate->nextState(STATE_AFTER_COLLECT_TREASURE_ACTIVATE_COLLECTORS);
        } else {
            $this->gamestate->nextState(STATE_END_AFTER_COLLECT_TREASURE_ACTIVATE_COLLECTORS);
        }
    }

    function stEndAfterCollectTreasureActivateCollectors() {
        $playersCustomOrderNo = $this->playerManager->getPlayerCustomOrderNo($this->getActivePlayerId());

        if ($playersCustomOrderNo == $this->getPlayerCount()) {
            // This was the last player, we check to see if this was the game end, otherwise we move on to the next round by passing the torch cards
            if ($this->treasureCardsManager->countCardsInDeck() > 0) {
                $this->servantDiceManager->resetAllEffortValues();
                $this->gamestate->nextState(STATE_PASS_TORCH_CARDS);
            } else  {
                $this->gamestate->nextState(STATE_BEFORE_GAME_END);
            }
        } else {
            // This was not the last player, so we go to the next player and see if they have any collectors to activate.
            $this->activeNextPlayer();
            $this->gamestate->nextState(STATE_AFTER_COLLECT_TREASURE);
        }
    }

    function stPassTorchCards() {
        self::debug("stPassTorchCards");

        $players = $this->loadPlayersBasicInfos();
        $this->playerManager->passTorchCards($players);
        $this->notificationsManager->notifyTorchCardsPassed();
        $this->gamestate->nextState(STATE_REVEAL_TREASURE);
    }

    function stBeforeGameEnd() {
        self::debug("stBeforeGameEnd");

        $players = $this->loadPlayersBasicInfos();
        foreach($players as $playerId => $player)
        {
            // Update the scores for all players
            $this->scoreManager->updateTotalScore($playerId, true);
        }

        // Break ties if necessary
        $this->scoreManager->breakTies();

        $finalScoring = [];
        foreach($players as $playerId => $player)
        {
            $finalScoring[$playerId] = $this->scoreManager->getScoreBreakDown($playerId);
        }

        $this->notificationsManager->notifyAllCardsFlipped($this->treasureCardsManager->flipAllCardsInPlayerAreas());
        $this->notificationsManager->notifyFinalScoring($finalScoring);

        $this->gamestate->nextState(STATE_GAME_END);
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
        
        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message. 
    */

    function zombieTurn( $state, $active_player )
    {
    	$statename = $state['name'];

        if ($state['type'] === "activeplayer") {
            $this->endTurn(false);

            return;
        }

        throw new feException( "Zombie mode not supported at this game state: ".$statename );
    }
    
///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */
    
    function upgradeTableDb( $from_version )
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345
        
        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        // Please add your future database scheme changes here
//
//


    }    
}
