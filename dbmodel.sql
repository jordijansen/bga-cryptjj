
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- CryptJj implementation : © Jordi Jansen <thestartplayer@gmail.com>
--
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

CREATE TABLE IF NOT EXISTS `treasure_cards` (
  `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `card_type` varchar(16) NOT NULL,
  `card_type_arg` int(11) NOT NULL,
  `card_location` varchar(100) NOT NULL,
  `card_location_arg` int(11) NOT NULL,
  `card_face_up` tinyint(1) NOT NULL default 0,
  `card_flipped` tinyint(1) NOT NULL default 0,
  PRIMARY KEY (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `servant_dice` (
  `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `card_type` varchar(16) NOT NULL,
  `card_type_arg` varchar(25) NOT NULL,
  `card_location` varchar(100) NOT NULL,
  `card_location_arg` int(11) NOT NULL,
  `card_effort` int(1) NULL,
  PRIMARY KEY (`card_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `collectors` (
                                            `id` varchar(25) NOT NULL,
                                            `treasure_type` varchar(25) NOT NULL,
                                            `side` varchar(1) NOT NULL,
                                            `ability_type` varchar(100) NOT NULL,
                                            `nr_of_cards_to_flip` int(1) NOT NULL,
                                            PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `player` ADD `custom_order` tinyint(1) NOT NULL default 0;
-- For two player games the leader also has the lights out card so we need to make sure this is the players second turn before progressing to next round
ALTER TABLE `player` ADD `has_played_before_this_round` tinyint(1) NOT NULL default 0;
-- The Manuscript B collector lets you view face-down cards in the display
ALTER TABLE `player` ADD `has_used_manuscript_b_this_round` tinyint(1) NOT NULL default 0;

