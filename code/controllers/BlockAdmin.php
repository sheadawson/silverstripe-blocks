<?php
/**
 * @package silverstipe blocks
 * @author Shea Dawson <shea@silverstripe.com.au>
 */
class BlockAdmin extends ModelAdmin {
    private static $managed_models = array(
    	'Block',
    	'BlockSet'
    );
	private static $url_segment = 'block-admin';
	private static $menu_title = "Blocks";
	
	public $showImportForm = false;
	
	private static $dependencies = array(
		'blockManager' => '%$blockManager',
	);
	public $blockManager;
	
	public function getManagedModels() {
		$models = parent::getManagedModels();
		
		// remove blocksets if not in use (set in config):
		if( ! $this->blockManager->getUseBlockSets() ){
			unset( $models['BlockSet'] );
		}
		
		return $models;
	}

	public function getEditForm($id = null, $fields = null) {
		$form = parent::getEditForm($id, $fields);	

		if($blockGridField = $form->Fields()->fieldByName('Block')){
			$blockGridField->setConfig(GridFieldConfig_BlockManager::create()
				->addBulkEditing()
//				->addComponent(new GridFieldDeleteAction())
				->removeComponentsByType('GridFieldEditButton')
				->removeComponentsByType('GridFieldDeleteAction')
				->removeComponentsByType('GridFieldDetailForm')
				->addComponent(new GridFieldDetailForm())
				//->addComponent(new GridFieldCopyButton())
				->addComponent(new GridFieldEditButton())
				->addComponent(new GridFieldDeleteAction())
			);
		}

		return $form;
	}
}