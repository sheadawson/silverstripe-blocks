<?php

class BlocksSiteConfigExtension extends DataExtension {

	private static $many_many = array(
		'Blocks' => 'Block'
	);

	private static $dependencies = array(
        'blockManager' => '%$blockManager',
    );

    public $blockManager;


    /**
	 * Block manager for SiteConfig
	 **/
	public function updateCMSFields(FieldList $fields) {
		if(count($this->blockManager->getAreasForTheme())){
	
			// Blocks related directly to this Page 
			$gridConfig = GridFieldConfig_BlockManager::create()
				->addExisting($this->owner->class)
				->addBulkEditing();

			$gridSource = $this->owner->Blocks();
			$fields->addFieldToTab('Root.Blocks', GridField::create('Blocks', 'Blocks', $gridSource, $gridConfig));

		}else{
			$fields->addFieldToTab('Root.Blocks', LiteralField::create('Blocks', 'This theme has no Block Areas configured.'));
		}
	}
}
