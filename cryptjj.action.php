<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * CryptJj implementation : © Jordi Jansen <thestartplayer@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 *
 * cryptjj.action.php
 *
 * CryptJj main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/cryptjj/cryptjj/myAction.html", ...)
 *
 */


class action_cryptjj extends APP_GameAction
{
    // Constructor: please do not modify
    public function __default()
    {
        if (self::isArg('notifwindow')) {
            $this->view = "common_notifwindow";
            $this->viewArgs['table'] = self::getArg("table", AT_posint, true);
        } else {
            $this->view = "cryptjj_cryptjj";
            self::trace("Complete reinitialization of board game");
        }
    }

    public function claimTreasure()
    {
        self::debug("claimTreasureAction");
        self::setAjaxMode();
        $args = self::getArg('actionArgs', AT_json, true);
        $this->validateJSonAlphaNum($args, 'actionArgs');
        $this->game->claimTreasure($args);
        self::ajaxResponse();
    }

    public function recoverServants()
    {
        self::debug("recoverServants");
        self::setAjaxMode();
        $this->game->recoverServants();
        self::ajaxResponse();
    }


    // TODO: defines your action entry points there



    public function validateJSonAlphaNum($value, $argName = 'unknown')
    {
        if (is_array($value)) {
            foreach ($value as $key => $v) {
                $this->validateJSonAlphaNum($key, $argName);
                $this->validateJSonAlphaNum($v, $argName);
            }
            return true;
        }
        if (is_int($value)) {
            return true;
        }
        $bValid = preg_match("/^[_0-9a-zA-Z- ]*$/", $value) === 1;
        if (!$bValid) {
            throw new BgaSystemException("Bad value for: $argName", true, true, FEX_bad_input_argument);
        }
        return true;
    }





    /*
    
    Example:
  	
    public function myAction()
    {
        self::setAjaxMode();     

        // Retrieve arguments
        // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
        $arg1 = self::getArg( "myArgument1", AT_posint, true );
        $arg2 = self::getArg( "myArgument2", AT_posint, true );

        // Then, call the appropriate method in your game logic, like "playCard" or "myAction"
        $this->game->myAction( $arg1, $arg2 );

        self::ajaxResponse( );
    }
    
    */

}
  

