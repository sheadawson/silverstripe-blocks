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
				->removeComponentsByType('GridFieldEditButton')
				->removeComponentsByType('GridFieldDeleteAction')
				->addComponent(new GridFieldEditButton())
				// at this stage deletes have to be done through the edit form becuase 
				// deleting published DataObjects causes issues with versioning
				//->addComponent(new GridFieldDeleteAction())
			);
		}

		return $form;
	}
}