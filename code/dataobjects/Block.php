<?php

class Block extends DataObject{

	private static $db = array(
		'Title' => 'Varchar',
		'Area' => 'Varchar',
		'Published' => 'Boolean',
	);

	private static $belongs_many_many = array(
		'Pages' => 'SiteTree'
	);

	private static $summary_fields = array(
		'Title' => 'Title', 
		'Area' => 'Area',
		'Published' => 'Published'
	);

	private static $areas = array();


	public function getCMSFields(){
		if(!$this->ID){
			$classes = ArrayLib::valuekey(ClassInfo::subclassesFor($this->class));
			unset($classes[$this->class]);

			return FieldList::create(
				TextField::create('Title', 'Title'),
				DropdownField::create('ClassName', 'Block Type', $classes)
			);
		}else{
			$fields = parent::getCMSFields();
			$fields->replaceField('Area', DropdownField::create('Area', 'Area', $this->getAreasFor($this->getCurrentTheme(), Controller::curr()->currentPage()->ClassName)));
			return $fields;
		}
	}


	public function getAreasFor($theme, $pageClass){
		$config = $this->config()->get('areas');
		$areas = $config[$this->getCurrentTheme()];
		return ArrayLib::valuekey(array_keys($areas));
		//return array_combine() array_keys($areas);
	}


	public function getCurrentTheme(){
		return Config::inst()->get('SSViewer', 'theme');
		return Multisites::inst()->getActiveSite()->Theme;
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

}