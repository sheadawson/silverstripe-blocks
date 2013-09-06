<?php
/**
 * @package silverstipe blocks
 * @author Shea Dawson <shea@silverstripe.com.au>
 */
class BlockSet extends DataObject {
	
	static $db = array(
		'Title' => 'Varchar',
		'PageTypes' => 'MultiValueField'
	);

	static $many_many = array(
		'Blocks' => 'Block'
	);

	public function getCMSFields(){
		$fields = parent::getCMSFields();

		$fields->addFieldToTab('Root.Main', HeaderField::create('SettingsHeading', 'Settings'), 'Title');
		$fields->addFieldToTab('Root.Main', MultiValueCheckboxField::create('PageTypes', 'Apply to Page Types:', $this->pageTypeOptions())->setDescription('Selected Page Types will inherit this Block Set automatically'));

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


	public function pageTypeOptions(){
		$classes = ClassInfo::subclassesFor('SiteTree');
		array_shift($classes);
		unset($classes['VirtualPage'], $classes['RedirectorPage']);
		foreach ($classes as $class) $return[$class] = singleton($class)->i18n_singular_name();
		return $return;
	}


	/**
	 * Get Blocks are published
	 * @return DataList
	 **/
	public function getPublishedBlocks(){
		return $this->Blocks()->filter('Published', 1);
	}
}
