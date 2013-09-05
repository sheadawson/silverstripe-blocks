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


	// public function getEditForm($id = null, $fields = null) {
	// 	$form = parent::getEditForm($id, $fields);
	// 	foreach ($form->Fields() as $field) {
	// 		var_dump($field->class);
	// 		var_dump($field->getName());
	// 	}
	// 	return $form;
	// }
}