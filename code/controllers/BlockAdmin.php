<?php
/**
 * @package silverstipe blocks
 * @author Shea Dawson <shea@silverstripe.com.au>
 */
class BlockAdmin extends ModelAdmin {
    static $managed_models = array(
    	'Block',
    	'BlockSet'
    );

	static $url_segment = 'block-admin';

	static $menu_title = "Blocks";
}