{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- CryptJj implementation : © Jordi Jansen <thestartplayer@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    cryptjj_cryptjj.tpl
    
    This is the HTML template of your game.
    
    Everything you are writing in this file will be displayed in the HTML page of your game user interface,
    in the "main game zone" of the screen.
    
    You can use in this template:
    _ variables, with the format {MY_VARIABLE_ELEMENT}.
    _ HTML block, with the BEGIN/END format
    
    See your "view" PHP file to check how to set variables and control blocks
    
    Please REMOVE this comment before publishing your game on BGA
-->

<div id="collectors-row" class="whiteblock">
    <h2>Collectors</h2>
</div>
<div id="exhausted-servants-row" class="whiteblock">
    <h2>Exhausted Servants</h2>
</div>
<div id="treasure-cards-row">
    <div id="treasure-cards-deck" class="whiteblock"><h2>Deck</h2></div>
    <div id="treasure-cards-display" class="whiteblock"><h2>Treasure Cards</h2></div>
</div>
<hr />
<div id="player-areas-row">
    <!-- player areas will be added here during set-up -->
</div>


<script type="text/javascript">

// Javascript HTML templates
var jstpl_player_area='<div class="player-area whiteblock" id="player-area-${id}" style="color: #${color}" id="player-area-${color}"><h2>${name}</h2><div class="card player-card-${color}-male"><div id="player-area-die-area-${id}" class="die-placement-area"></div></div></div>';
var jstpl_treasure_deck='<div class="card treasure-card-${topCardType}-back"><h2 class="deck-count">${size}</h2></div>';
var jstpl_treasure_card='<div id="treasure-card-${id}" class="card treasure-card-${type}-${value}"></div>';

var jstpl_die_1='<div class="die die-${color}"><span class="pip"></span></div>';
var jstpl_die_2='<div class="die die-${color}"><span class="pip"></span><span class="pip"></span></div>';
var jstpl_die_3='<div class="die die-${color}"><span class="pip"></span><span class="pip"></span><span class="pip"></span></div>';
var jstpl_die_4='<div class="die die-${color}"><span class="pip"></span><span class="pip"></span><span class="pip"></span><span class="pip"></span></div>';
var jstpl_die_5='<div class="die die-${color}"><span class="pip"></span><span class="pip"></span><span class="pip"></span><span class="pip"></span><span class="pip"></span></div>';
var jstpl_die_6='<div class="die die-${color}"><span class="pip"></span><span class="pip"></span><span class="pip"></span><span class="pip"></span><span class="pip"></span><span class="pip"></span></div>';


</script>  

{OVERALL_GAME_FOOTER}
