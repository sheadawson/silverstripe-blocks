<?php
//define global path to Components' root folder
if(!defined('BLOCK_PATH')) define('BLOCK_PATH', rtrim(basename(dirname(__FILE__))));

Config::inst()->update('LeftAndMain','extra_requirements_css', array(BLOCK_PATH . '/css/block-admin.css'));