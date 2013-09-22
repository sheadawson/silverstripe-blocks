<?php
/**
 * @package silverstipe blocks
 * @author Shea Dawson <shea@silverstripe.com.au>
 */
class Block extends DataObject implements PermissionProvider{

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
			$currentPage = Controller::curr()->currentPage();
			$areasFieldSource = $this->blockManager->getAreasForPageType($currentPage->ClassName);	
			$areasPreviewButton = LiteralField::create('PreviewLink', $currentPage->areasPreviewButton());
		}else{
			$areasFieldSource = $this->blockManager->getAreasForTheme();
			$areasPreviewButton = false;	
		}
		
		$areasField = DropdownField::create('Area', 'Area', $areasFieldSource);
		
		if(!$this->ID){
			$classes = ArrayLib::valuekey(ClassInfo::subclassesFor('Block'));
			unset($classes['Block']);

			$fields = array(
				TextField::create('Title', 'Title'),
				DropdownField::create('ClassName', 'Block Type', $classes),
				$areasField->setRightTitle($areasPreviewButton),
			);
			
			return FieldList::create($fields);

		}else{
			$fields = parent::getCMSFields();
			$pageClass = null;
			$controller = Controller::curr();		
			$fields->replaceField('Area', $areasField->setRightTitle($areasPreviewButton));

			$fields->removeFieldFromTab('Root', 'SiteConfigs');
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


	/**
	 * Renders this block with appropriate templates
	 * looks for templates that match BlockClassName_AreaName 
	 * falls back to BlockClassName
	 **/
	public function forTemplate(){
		// TODO standard render with always seemed to default to $this->ClassName template
		// so having to use SSViewer::hasTemplate() here
		if($this->Area){
			$template[] = $this->class . '_' . $this->Area;
			if(SSViewer::hasTemplate($template)){
				return $this->renderWith($template);
			}
		}

		return $this->renderWith($this->ClassName);
	}


	public function BlockHTML(){
		return $this->forTemplate();
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
			'BLOCK_EDIT' => array(
				'name' => 'Edit a Block',
				'category' => 'Blocks',
			),
			'BLOCK_DELETE' => array(
				'name' => 'Delete a Block',
				'category' => 'Blocks',
			),
			'BLOCK_CREATE' => array(
				'name' => 'Create a Block',
				'category' => 'Blocks'
			)
		);
	}

}