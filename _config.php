<?php
//define global path to Components' root folder
if (!defined('BLOCKS_DIR')) {
    define('BLOCKS_DIR', rtrim(basename(dirname(__FILE__))));
}

if (!class_exists('SS_Object')) class_alias('Object', 'SS_Object');

Config::inst()->update('LeftAndMain', 'extra_requirements_javascript', array(BLOCKS_DIR.'/javascript/blocks-cms.js'));
Config::inst()->update('BlockAdmin', 'menu_icon', BLOCKS_DIR.'/images/blocks.png');
