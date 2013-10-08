<?php
/**
 * @package silverstipe blocks
 * @author Shea Dawson <shea@silverstripe.com.au>
 */
class BlockSet extends DataObject implements PermissionProvider{
	
	static $db = array(
		'Title' => 'Varchar',
		'PageTypes' => 'MultiValueField'
	);

	static $many_many = array(
		'Blocks' => 'Block',
		'PageParents' => 'SiteTree'
	);

	public function getCMSFields(){
		$fields = parent::getCMSFields();

		$fields->addFieldToTab('Root.Main', HeaderField::create('SettingsHeading', 'Settings'), 'Title');
		$fields->addFieldToTab('Root.Main', MultiValueCheckboxField::create('PageTypes', 'Only apply to these Page Types:', $this->pageTypeOptions())->setDescription('Selected Page Types will inherit this Block Set automatically'));
		$fields->addFieldToTab('Root.Main', TreeMultiselectField::create('PageParents', 'Only apply to children of these Pages:', 'SiteTree'));

		if(!$this->ID){
			$fields->addFieldToTab('Root.Main', LiteralField::create('NotSaved', "<p class='message warning'>You can add Blocks to this set once you have saved it for the first time</p>"));
			return $fields;
		}

		$fields->removeFieldFromTab('Root', 'Blocks');
		$gridConfig = GridFieldConfig_BlockManager::create(true);
		$gridSource = $this->Blocks();
		$fields->addFieldToTab('Root.Main', HeaderField::create('BlocksHeading', 'Blocks'));
		$fields->addFieldToTab('Root.Main', GridField::create('Blocks', 'Blocks', $gridSource, $gridConfig));

		return $fields;
	}


	/**
	 * Returns a sorted array suitable for a dropdown with pagetypes and their translated name
	 * 
	 * @return array
	 */
	protected function pageTypeOptions() {
		$pageTypes = array();
		foreach(SiteTree::page_type_classes() as $pageTypeClass) {
			$pageTypes[$pageTypeClass] = singleton($pageTypeClass)->i18n_singular_name();
		}
		asort($pageTypes);
		return $pageTypes;
	}


	/**
	 * Get Blocks are published
	 * @return DataList
	 **/
	public function getPublishedBlocks(){
		return $this->Blocks()->filter('Published', 1);
	}


	public function canView($member = null){
		return true;
	}

	public function canEdit($member = null) {
		return Permission::check('ADMIN') || Permission::check('BLOCK_EDIT');
	}

	public function canDelete($member = null) {
		return Permission::check('ADMIN') || Permission::check('BLOCK_DELETE');
	}

	public function canCreate($member = null) {
		return Permission::check('ADMIN') || Permission::check('BLOCK_CREATE');
	}

	public function providePermissions() {
		return array(
			'BLOCKSET_EDIT' => array(
				'name' => 'Edit a Block Set',
				'category' => 'Blocks',
			),
			'BLOCKSET_DELETE' => array(
				'name' => 'Delete a Block Set',
				'category' => 'Blocks',
			),
			'BLOCKSET_CREATE' => array(
				'name' => 'Create a Block Set',
				'category' => 'Blocks'
			)
		);
	}
}
