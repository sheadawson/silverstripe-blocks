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


	// public function getInheritedFrom(){
	// 	if(Controller::curr()->class == 'CMSPageEditController'){
	// 		if($page = Controller::curr()->currentPage()){
	// 			if(!$this->Pages()->filter('ID', $page->ID)->count()){
	// 				return 'Inherited';
	// 			}else{
	// 				return '-';
	// 			}
	// 		}
	// 	}
	// }

	public function onBeforeDelete(){
		parent::onBeforeDelete();
		$this->Pages()->removeAll();
		$this->BlockSets()->removeAll();
	}

}