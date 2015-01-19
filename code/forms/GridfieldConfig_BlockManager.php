<?php
/**
 * GridFieldConfig_BlockManager
 * Provides a reusable GridFieldConfig for managing Blocks
 * @package silverstipe blocks
 * @author Shea Dawson <shea@livesource.co.nz>
 */
class GridFieldConfig_BlockManager extends GridFieldConfig{

	public $blockManager;

	public function __construct($canAdd = true, $canEdit = true, $canDelete = true, $editableRows = false, $aboveOrBelow = false) {
		parent::__construct();

		$this->blockManager = Injector::inst()->get('BlockManager');
		$controllerClass = Controller::curr()->class;
		// Get available Areas (for page) or all in case of ModelAdmin
		if($controllerClass == 'CMSPageEditController'){
			$currentPage = Controller::curr()->currentPage();
			$areasFieldSource = $this->blockManager->getAreasForPageType($currentPage->ClassName);
		} else {
			$areasFieldSource = $this->blockManager->getAreasForTheme();
		}
		
		// EditableColumns only makes sense on Saveable parenst (eg Page), or inline changes won't be saved
		if($editableRows){
			$this->addComponent($editable = new GridFieldEditableColumns());
			$displayfields = array(
				'singular_name' => array('title' => 'Block Type', 'field' => 'ReadonlyField'),
				'Title'        	=> array('title' => 'Title', 'field' => 'ReadonlyField'),
				'BlockArea'	=> array(	
					'title' => 'Block Area
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
						// the &nbsp;s prevent wrapping of dropdowns
					'callback' => function() use ($areasFieldSource){
							return DropdownField::create('BlockArea', 'Block Area', $areasFieldSource)
								->setHasEmptyDefault(true);
						}
				),
				'isPublishedNice'	=> array('title' => 'Published', 'field' => 'ReadonlyField'),
				'UsageListAsString' => array('title' => 'Used on', 'field' => 'ReadonlyField'),
			);

			if($aboveOrBelow){
				$displayfields['AboveOrBelow'] = array(
					'title' => 'Above or Below',
					'callback' => function() {
						return DropdownField::create('AboveOrBelow', 'Above or Below', BlockSet::config()->get('above_or_below_options'));
					}
				);
			}
			$editable->setDisplayFields($displayfields);
		} else {
			$this->addComponent($dcols = new GridFieldDataColumns());
			
			$displayfields = array(
				'singular_name' => 'Block Type',
				'Title' => 'Title',
				'BlockArea' => 'Block Area',
				'isPublishedNice' => 'Published',
				'UsageListAsString' => 'Used on'
			);
			$dcols->setDisplayFields($displayfields);
			$dcols->setFieldCasting(array("UsageListAsString"=>"HTMLText->Raw"));
		}
		
		$this->addComponent(new GridFieldButtonRow('before'));
		$this->addComponent(new GridFieldToolbarHeader());
		$this->addComponent(new GridFieldDetailForm());
		$this->addComponent($sort = new GridFieldSortableHeader());
		$this->addComponent($filter = new GridFieldFilterHeader());
		$this->addComponent(new GridFieldDetailForm());
		if($controllerClass == 'BlockAdmin' && class_exists('GridFieldCopyButton')){
			$this->addComponent(new GridFieldCopyButton());
		}

		$filter->setThrowExceptionOnBadDataType(false);
		$sort->setThrowExceptionOnBadDataType(false);

		if($canAdd){
			$multiClass = new GridFieldAddNewMultiClass();
			$classes = ArrayLib::valuekey(ClassInfo::subclassesFor('Block'));
			array_shift($classes);
			foreach ($classes as $k => $v) {
				$classes[$k] = singleton($k)->singular_name();
			}
			$multiClass->setClasses($classes);
			$this->addComponent($multiClass);	
			//$this->addComponent(new GridFieldAddNewButton());	
		}
		
		if($canEdit){
			$this->addComponent(new GridFieldEditButton());	
		}

		if($canDelete){
			$this->addComponent(new GridFieldDeleteAction(true));
		}

		return $this;		
		
	}


	/**
	 * Add the GridFieldAddExistingSearchButton component to this grid config
	 * @return $this
	 **/
	public function addExisting(){
		$this->addComponent($add = new GridFieldAddExistingSearchButton());
		$add->setSearchList(Block::get());
		return $this;
	}

	/**
	 * Add the GridFieldBulkManager component to this grid config
	 * @return $this
	 **/
	public function addBulkEditing(){
		if(class_exists('GridFieldBulkManager')){
			$this->addComponent(new GridFieldBulkManager());
		}
		return $this;
	}
}
