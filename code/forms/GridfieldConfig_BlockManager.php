<?php

class GridFieldConfig_BlockManager extends GridFieldConfig{

	/**
	 * @param int $itemsPerPage - How many items per page should show up
	 */
	public function __construct() {
		
		$this->addComponent(new GridFieldButtonRow('before'));
		$this->addComponent(new GridFieldAddNewButton('buttons-before-left'));
		$this->addComponent(new GridFieldAddExistingAutocompleter('buttons-before-left'));
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
			'Area' 			=> array('title' => 'Area', 'field' => 'ReadonlyField'),
			'Weight'    	=> array('title' => 'weight', 'field' => 'NumericField'),
			'Published' 	=> 'Published'
		));
	}
}