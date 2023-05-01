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
<span class="crypt-icon treasure-type small treasure-idol"></span>
<span class="crypt-icon treasure-type small treasure-jewelery"></span>
<span class="crypt-icon treasure-type small treasure-manuscript"></span>
<span class="crypt-icon treasure-type small treasure-pottery"></span>
<span class="crypt-icon treasure-type small treasure-remains"></span>
<span class="crypt-icon treasure-type small treasure-tapestry"></span>

<span class="crypt-icon coin small">3</span>

<div class="whiteblock">
    <div id="collector-cards"></div>
</div>
<div class="ui-row">
    <div id="treasure-cards-deck" class="whiteblock"></div>
    <div id="exhausted-servants-wrapper" class="whiteblock">
        <div id="exhausted-servants-background">
            <div id="exhausted-servants-text"></div>
            <div id="exhausted-servants">

            </div>
        </div>
    </div>
    <div class="whiteblock">
        <div id="treasure-cards-discard">

        </div>
    </div>
</div>
<div class="whiteblock">
    <div id="treasure-cards-display"></div>
</div>
<hr />
<div id="player-areas-row">
    <!-- player areas will be added here during set-up -->
</div>


<script type="text/javascript">

// Javascript HTML templates
var jstpl_player_area='<div class="player-area whiteblock" id="player-area-${player_id}" style="color: #${color}">\
    <h2>${name}</h2>\
    <div style="display: flex;">\
        <div class="card player-card-${color}-male">\
            <div class="dice-placement-area lower">\
            </div>\
        </div>\
        <div class="player-treasure-areas">\
            <div class="player-treasure-area"><div id="player-${player_id}-treasure-idol"></div></div>\
            <div class="player-treasure-area"><div id="player-${player_id}-treasure-jewelery"></div></div>\
            <div class="player-treasure-area"><div id="player-${player_id}-treasure-manuscript"></div></div>\
            <div class="player-treasure-area"><div id="player-${player_id}-treasure-pottery"></div></div>\
            <div class="player-treasure-area"><div id="player-${player_id}-treasure-remains"></div></div>\
            <div class="player-treasure-area"><div id="player-${player_id}-treasure-tapestry"></div></div>\
        </div>\
    </div>\
</div>';
var jstpl_treasure_deck='<div class="card treasure-card-${topCardType}-back"><h2 class="deck-count">${size}</h2></div>';
var jstpl_treasure_card='<div data-id="${id}" id="treasure-card-${id}" class="will-be-set-by-code">\
    <div class="button-container" style="padding-top: 70px;"> \
        <div class="row"><a href="#" data-id="${id}" id="increase-dice-${id}" class="bgabutton bgabutton_blue"><span>+</span></a></div>\
        <div class="row">\
            <div data-id="${id}" id="dice-selection-area-${id}" class="dice-selection-area row">\
                <div class="die-placeholder"><span class="pip-placeholder">+</span></div>\
                <div class="die-placeholder"><span class="pip-placeholder">+</span></div>\
                <div class="die-placeholder"><span class="pip-placeholder">+</span></div>\
            </div>\
        </div>\
        <div class="row"><a href="#" data-id="${id}" id="decrease-dice-${id}" class="bgabutton bgabutton_blue"><span>-</span></a></div>\
    </div>\
    <div class="dice-placement-area lower">\
    </div>\
<div>';

var jstpl_treasure_card_tooltip='<div class="treasure-card-tooltip">\
<div class="card treasure-card treasure-card-${type}-${value}"></div>\
<hr/>\
${text}\
<div>';

var jstpl_die='<div data-id="${id}" id="servant-die-${id}" class="dice servant-die">\
<div class="color-${color} side side1">\
</div>\
<div class="color-${color} side side2">\
</div>\
<div class="color-${color} side side3">\
</div>\
<div class="color-${color} side side4">\
</div>\
<div class="color-${color} side side5">\
</div>\
<div class="color-${color} side side6">\
</div>\
</div>';

var jstpl_torch_card_container='<div id="torch-cards-${playerId}"></div>';
var jstpl_torch_card='<div id="${type}-card" class="card small torch-card-${type}"></div>';
var jstpl_torch_card_tooltip='<div class="torch-card-tooltip">\
<div class="card torch-card-${type}"></div>\
<hr/>\
${text}\
<div>';

var jstpl_collector_card='<div data-id="${id}" id="collector-card-${id}" class="card collector-card collector-card-${id}"><div>';

var jstpl_icon_treasure='<span class="crypt-icon treasure-type small treasure-${type}"></span>'
var jstpl_icon_coin='<span class="crypt-icon coin small">${value}</span>'
var jstpl_icon_dice='<span class="color-${color} side${value} crypt-icon die small"></span>'
var jstpl_icon_torch='<span class="crypt-icon torch small torch-card-${type}"></span>';


</script>

{OVERALL_GAME_FOOTER}
