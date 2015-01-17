<?php
//define global path to Components' root folder
if(!defined('BLOCKS_DIR')) define('BLOCKS_DIR', rtrim(basename(dirname(__FILE__))));

Config::inst()->update('LeftAndMain','extra_requirements_javascript', array(BLOCKS_DIR . '/javascript/blocks-cms.js'));