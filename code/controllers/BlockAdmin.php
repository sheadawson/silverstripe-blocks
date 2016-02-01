<?php
/**
 * BlockAdmin
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


	/**
	 * @return array
	 **/
	public function getManagedModels() {
		$models = parent::getManagedModels();

		// remove blocksets if not in use (set in config):
		if(!$this->blockManager->getUseBlockSets()) {
			unset( $models['BlockSet'] );
		}

		return $models;
	}


	/**
	 * @return Form
	 **/
	public function getEditForm($id = null, $fields = null) {
		Versioned::reading_stage('Stage');
		$form = parent::getEditForm($id, $fields);

		if($blockGridField = $form->Fields()->fieldByName('Block')) {
			$blockGridField->setConfig(GridFieldConfig_BlockManager::create(true, true, false));
			$config = $blockGridField->getConfig();
			$dcols = $config->getComponentByType('GridFieldDataColumns');
			$dfields = $dcols->getDisplayFields($blockGridField);
			unset($dfields['BlockArea']);
			$dcols->setDisplayFields($dfields);
		}

		return $form;
	}
}