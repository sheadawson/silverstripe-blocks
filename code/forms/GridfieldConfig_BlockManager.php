<?php

class GridFieldConfig_BlockManager extends GridFieldConfig{

    public $blockManager;

	/**
	 * @param int $itemsPerPage - How many items per page should show up
	 */
	public function __construct($addExisting = true, $pageClass = null) {
		parent::__construct();

		$this->blockManager = Injector::inst()->get('BlockManager');
		
		$this->addComponent(new GridFieldButtonRow('before'));
		$this->addComponent(new GridFieldAddNewButton('buttons-before-left'));
		$this->addComponent(new GridFieldToolbarHeader());
		$this->addComponent($sort = new GridFieldSortableHeader());
		$this->addComponent($filter = new GridFieldFilterHeader());
		$this->addComponent($editable = new GridFieldEditableColumns());
		$this->addComponent(new GridFieldEditButton());
		$this->addComponent(new GridFieldDeleteAction(true));
		$this->addComponent(new GridFieldDeleteAction());
		$this->addComponent(new GridFieldDetailForm());
		
		$filter->setThrowExceptionOnBadDataType(false);
		$sort->setThrowExceptionOnBadDataType(false);

		$editable->setDisplayFields(array(
			'Title'        	=> array('title' => 'Title', 'field' => 'ReadonlyField'),
			'ClassName' 	=> array('title' => 'Block Type', 'field' => 'ReadonlyField'),
			'Area' 			=> array('title' => 'Blcok Area', 'field' => 'ReadonlyField'),
			'InheritedFrom' => array('title' => 'Inhereted From', 'field' => 'ReadonlyField'),
			'Weight'    	=> array('title' => 'Weight', 'field' => 'NumericField'),
			//'PagesCount'  	=> array('title' => 'Number of Pages', 'field' => 'ReadonlyField'),
			'Published' 	=> array('title' => 'Published (global)', 'field' => 'CheckboxField'),
			//'PublishedOnThisPage' => array('title' => 'Published (just for this page)', 'callback' => $this->getPublishedOnThisPageField()),
		));

		// if($addExisting){
		// 	$this->addComponent($add = new GridFieldAddExistingSearchButton());

		// 	if($pageClass){
		// 		$areas = $this->blockManager->getAreasForPageType($pageClass);	
		// 	}else{
		// 		$areas = $this->blockManager->getAreasForTheme();	
		// 	}
		
		// 	$add->setSearchList(ArrayList::create(Block::get()->filter('Area', $areas)->toArray()));
		// }
		
	}

}