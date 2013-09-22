<?php

class GridFieldConfig_BlockManager extends GridFieldConfig{

    public $blockManager;

	public function __construct($canAdd = true, $canEdit = true, $canDelete = true) {
		parent::__construct();

		$this->blockManager = Injector::inst()->get('BlockManager');
		
		$this->addComponent($editable = new GridFieldEditableColumns());
		$editable->setDisplayFields(array(
			'ClassName' 	=> array('title' => 'Block Type', 'field' => 'ReadonlyField'),
			'Title'        	=> array('title' => 'Title', 'field' => 'ReadonlyField'),
			'Area' 			=> array('title' => 'Block Area', 'field' => 'ReadonlyField'),
			'InheritedFrom' => array('title' => 'Inherited From', 'field' => 'ReadonlyField'),
			'Weight'    	=> array('title' => 'Weight', 'field' => 'NumericField'),
			//'PagesCount'  	=> array('title' => 'Number of Pages', 'field' => 'ReadonlyField'),
			'Published' 	=> array('title' => 'Published (global)', 'field' => 'CheckboxField'),
			//'PublishedOnThisPage' => array('title' => 'Published (just for this page)', 'callback' => $this->getPublishedOnThisPageField()),
		));

		$this->addComponent(new GridFieldButtonRow('before'));
		$this->addComponent(new GridFieldToolbarHeader());
		$this->addComponent(new GridFieldDetailForm());
		$this->addComponent($sort = new GridFieldSortableHeader());
		$this->addComponent($filter = new GridFieldFilterHeader());

		$filter->setThrowExceptionOnBadDataType(false);
		$sort->setThrowExceptionOnBadDataType(false);

		if($canAdd){
			$this->addComponent(new GridFieldAddNewButton('buttons-before-left'));	
		}
		
		if($canEdit){
			$this->addComponent(new GridFieldEditButton());	
		}

		if($canDelete){
			$this->addComponent(new GridFieldDeleteAction(true));
		}

		return $this;		
		
	}

	public function addExisting($pageClass = null){
		$this->addComponent($add = new GridFieldAddExistingSearchButton());

		if($pageClass){
			$areas = $this->blockManager->getAreasForPageType($pageClass);	
		}else{
			$areas = $this->blockManager->getAreasForTheme();	
		}
	
		$list = Block::get()->filter('Area', $areas);

		// TODO find a more appropriate way of doing this
		if(Block::has_extension('MultisitesAware')){
			$list = $list->filter('SiteID', Multisites::inst()->getActiveSite()->ID);
		}

		$add->setSearchList($list);

		return $this;
	}


	public function addBulkEditing(){
		if(class_exists('GridFieldBulkManager')){
			$this->addComponent(new GridFieldBulkManager());
		}
		return $this;
	}

}