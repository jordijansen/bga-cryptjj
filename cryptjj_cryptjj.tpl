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

<div id="oversurface"></div>

<div class="ui-row">
    <div id="collector-cards" class="whiteblock"><h2>Collectors</h2></div>
</div>
<div class="ui-row">
    <div id="treasure-cards-deck" class="whiteblock"><h2>Deck</h2></div>
    <div id="exhausted-servants" class="whiteblock">
        <h2>Exhausted Servants</h2>
        <img src="{GAMETHEMEURL}img/exhausted-servants.png" />
    </div>
    <div id="treasure-cards-discard" class="whiteblock"><h2>Discard</h2></div>
</div>
<div class="ui-row">
    <div id="treasure-cards-display" class="whiteblock">
    </div>
</div>
<hr />
<div id="player-areas-row">
    <!-- player areas will be added here during set-up -->
</div>


<script type="text/javascript">

// Javascript HTML templates
var jstpl_player_area='<div class="player-area whiteblock" id="player-area-${id}" style="color: #${color}" id="player-area-${color}">\
    <h2>${name}</h2>\
    <div class="card player-card-${color}-male">\
        <div class="dice-placement-area lower">\
        </div>\
    </div>\
</div>';
var jstpl_treasure_deck='<div class="card treasure-card-${topCardType}-back"><h2 class="deck-count">${size}</h2></div>';
var jstpl_treasure_card='<div data-id="${id}" id="treasure-card-${id}" class="card treasure-card treasure-card-${type}-${value}">\
    <div class="row" style="padding-top: 55px;"><a href="#" data-id="${id}" id="increase-dice-${id}" class="bgabutton bgabutton_blue"><span>+</span></a></div>\
    <div class="dice-selection-area row">\
    <div class="die die-placeholder"><span class="pip-placeholder">+</span></div>\
    <div class="die die-placeholder"><span class="pip-placeholder">+</span></div>\
    <div class="die die-placeholder"><span class="pip-placeholder">+</span></div>\
    </div>\
    <div class="row"><a href="#" data-id="${id}" id="decrease-dice-${id}" class="bgabutton bgabutton_blue"><span>-</span></a></div>\
    <div class="dice-placement-area lower">\
    </div>\
<div>';

var jstpl_die='<div data-id="${id}" id="servant-die-${id}" class="die die-${color}"></div>';

</script>  

{OVERALL_GAME_FOOTER}
