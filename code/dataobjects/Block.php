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
		'Weight' => 'Int'
	);

	private static $belongs_many_many = array(
		'Pages' => 'SiteTree',
		'BlockSets' => 'BlockSet'
	);

	private static $summary_fields = array(
		'Title' => 'Title', 
		'Area' => 'Area',
		'Published' => 'Published'
	);

	private static $default_sort = 'Weight';

	private static $dependencies = array(
        'blockConfig' => '%$blockConfig',
    );

    public $blockConfig;


	public function getCMSFields(){
		if(!$this->ID){
			$classes = ArrayLib::valuekey(ClassInfo::subclassesFor($this->class));
			unset($classes[$this->class]);

			return FieldList::create(
				TextField::create('Title', 'Title'),
				DropdownField::create('ClassName', 'Block Type', $classes)
			);
		
			
		}

		$fields = parent::getCMSFields();
		
		$pageClass = null;

		$controller = Controller::curr();
		if($controller->class == 'BlockAdmin' && $controller->modelClass == 'BlockSet'){
			// TODO filter areasForPageClass by the page types defined on the BlockSet
			$fields->replaceField('Area', DropdownField::create('Area', 'Area', $this->blockConfig->getAreasForTheme()));			
		}else{
			$fields->replaceField('Area', DropdownField::create('Area', 'Area', $this->blockConfig->getAreasForPageClass()));
		}
		

		return $fields;

		
		
	}


	// public function getCurrentTheme(){
	// 	return Config::inst()->get('SSViewer', 'theme');
	// 	return Multisites::inst()->getActiveSite()->Theme;
	// }


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