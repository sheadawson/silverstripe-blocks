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
	
	public $blockManager = false;
	
	public function __construct() {
		parent::__construct();

		$this->blockManager = Injector::inst()->get('BlockManager');
		
	}
	
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
		
		// instantiate blockManager only once
//		if( ! $this->blockManager ) { 
//			$this->blockManager = Injector::inst()->get('BlockManager');
//		}
		

		if($blockGridField = $form->Fields()->fieldByName('Block')){
			$blockGridField->setConfig(GridFieldConfig_BlockManager::create()
				->addBulkEditing()
//				->addComponent(new GridFieldDeleteAction())
				->removeComponentsByType('GridFieldEditButton')
				->removeComponentsByType('GridFieldDeleteAction')
				->removeComponentsByType('GridFieldDetailForm')
				->addComponent(new GridFieldDetailFormCustom())
				//->addComponent(new GridFieldCopyButton())
				->addComponent(new GridFieldEditButton())
				->addComponent(new GridFieldDeleteAction())
			);
		}

		return $form;
	}
}