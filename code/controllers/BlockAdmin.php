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
	public $showImportForm = false;

	public function getEditForm($id = null, $fields = null) {
		$form = parent::getEditForm($id, $fields);

		if($blockGridField = $form->Fields()->fieldByName('Block')){
			$blockGridField->setConfig(GridFieldConfig_BlockManager::create()
				->addBulkEditing()
//				->addComponent(new GridFieldDeleteAction())
				->removeComponentsByType('GridFieldEditButton')
				->removeComponentsByType('GridFieldDeleteAction')
				->removeComponentsByType('GridFieldDetailForm')
				->addComponent(new GridFieldDetailFormCustom())
				->addComponent(new GridFieldCopyButton())
				->addComponent(new GridFieldEditButton())
				->addComponent(new GridFieldDeleteAction())
			);
		}

		return $form;
	}
}