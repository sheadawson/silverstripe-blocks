<?php
/**
 * @package silverstipe blocks
 * @author Shea Dawson <shea@silverstripe.com.au>
 */
class Block extends DataObject{

	private static $db = array(
		'Title' => 'Varchar',
		'Area' => 'Varchar',
		'Published' => 'Boolean',
		'Weight' => 'Int',
		"CanViewType" => "Enum('Anyone, LoggedInUsers, OnlyTheseUsers', 'Anyone')",
	);

	private static $many_many = array(
		"ViewerGroups" => "Group"
	);

	private static $belongs_many_many = array(
		'Pages' => 'SiteTree',
		'SiteConfigs' => 'SiteConfig',
		'BlockSets' => 'BlockSet'
	);

	// private static $summary_fields = array(
	// 	'Title' => 'Title', 
	// 	'Area' => 'Area',
	// 	'Published' => 'Published',
	// );

	private static $default_sort = array('Area'=>'ASC', 'Weight'=>'ASC', 'Title' => 'ASC');

	private static $dependencies = array(
        'blockManager' => '%$blockManager',
    );

    public $blockManager;

	public function getCMSFields(){
		
		if(Controller::curr()->class == 'CMSPageEditController'){
			$areasFieldSource = $this->blockManager->getAreasForPageType(Controller::curr()->currentPage()->ClassName);	
		}else{
			$areasFieldSource = $this->blockManager->getAreasForTheme();	
		}
		
		$areasField = DropdownField::create('Area', 'Area', $areasFieldSource);
		
		if(!$this->ID){
			//var_dump($this->class);
			$classes = ArrayLib::valuekey(ClassInfo::subclassesFor('Block'));
			unset($classes['Block']);

			return FieldList::create(
				TextField::create('Title', 'Title'),
				DropdownField::create('ClassName', 'Block Type', $classes),
				$areasField
			);

		}else{
			$fields = parent::getCMSFields();
			$pageClass = null;
			$controller = Controller::curr();		
			$fields->replaceField('Area', $areasField);
			$fields->dataFieldByName('Weight')->setRightTitle('Controls block ordering. A small weight value will float, a large will sink.');

			// Viewer groups
			$fields->removeFieldFromTab('Root', 'ViewerGroups');
			$groupsMap = Group::get()->map('ID', 'Breadcrumbs')->toArray();
			asort($groupsMap);
			$viewersOptionsField = new OptionsetField(
				"CanViewType", 
				_t('SiteTree.ACCESSHEADER', "Who can view this page?")
			);
			$viewerGroupsField = ListboxField::create("ViewerGroups", _t('SiteTree.VIEWERGROUPS', "Viewer Groups"))
				->setMultiple(true)
				->setSource($groupsMap)
				->setAttribute(
					'data-placeholder', 
					_t('SiteTree.GroupPlaceholder', 'Click to select group')
			);
			$viewersOptionsSource = array();
			$viewersOptionsSource["Anyone"] = _t('SiteTree.ACCESSANYONE', "Anyone");
			$viewersOptionsSource["LoggedInUsers"] = _t('SiteTree.ACCESSLOGGEDIN', "Logged-in users");
			$viewersOptionsSource["OnlyTheseUsers"] = _t('SiteTree.ACCESSONLYTHESE', "Only these people (choose from list)");
			$viewersOptionsField->setSource($viewersOptionsSource);

			$fields->addFieldsToTab('Root.ViewerGroups', array(
				$viewersOptionsField,
				$viewerGroupsField,
			));

			return $fields;
		}
		
	}


	public function validate() {
		$result = parent::validate();

		if(!$this->Title){
			$result->error('Block Title is required');
		}
		return $result;
	}


	public function forTemplate(){
		return $this->renderWith($this->class);
	}


	public function PagesCount(){
		return $this->Pages()->count();
	}


	public function onBeforeDelete(){
		parent::onBeforeDelete();
		$this->Pages()->removeAll();
		$this->BlockSets()->removeAll();
	}


	public function canView($member = null){
		if(!$member || !(is_a($member, 'Member')) || is_numeric($member)) {
			$member = Member::currentUserID();
		}

		// admin override
		if($member && Permission::checkMember($member, array("ADMIN", "SITETREE_VIEW_ALL"))) return true;

		// Standard mechanism for accepting permission changes from extensions
		$extended = $this->extendedCan('canView', $member);
		if($extended !== null) return $extended;

		// check for empty spec
		if(!$this->CanViewType || $this->CanViewType == 'Anyone') return true;

		// check for any logged-in users
		if($this->CanViewType == 'LoggedInUsers' && $member) {
			return true;
		}

		// check for specific groups
		if($member && is_numeric($member)) $member = DataObject::get_by_id('Member', $member);
		if($this->CanViewType == 'OnlyTheseUsers' && $member && $member->inGroups($this->ViewerGroups())){
			return true;	
		} 
		
		return false;
	}

}