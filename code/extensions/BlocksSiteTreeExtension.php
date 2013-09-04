<?php
/**
 * @package silverstipe blocks
 * @author Shea Dawson <shea@silverstripe.com.au>
 */
class BlocksSiteTreeExtension extends SiteTreeExtension{

	private static $db = array(
		'InheritBlocks' => 'Boolean',
		'ExcludeInheritedBlocks' => 'MultiValueField'
	);

	private static $many_many = array(
		'Blocks' => 'Block'
	);

	// private static $many_many_extraFields = array(
	// 	'Sort' => 'Int'
	// );

	private static $defaults = array(
    	'InheritBlocks' => 1
    );

	private static $dependencies = array(
        'blockConfig' => '%$blockConfig',
    );

    public $blockConfig;


	/**
	 * Block manager for Pages
	 **/
	public function updateCMSFields(FieldList $fields) {
		if(count($this->blockConfig->getAreasForPageClass($this->owner->ClassName))){
	
			// Blocks related directly to this Page 
			$fields->addFieldToTab('Root.Blocks', GridField::create('Blocks', 'Blocks', $this->owner->Blocks(), GridFieldConfig_BlockManager::create()));

			// Blocks inherited from BlockSets 
			$fields->addFieldToTab('Root.Blocks', HeaderField::create('InheritedFromSets', 'Block Sets'));
			$blockSets = $this->getAppliedSets()->column('Title');
			if(count($blockSets)){
				$blockSetsMessage = 'This page inherits blocks from the following BlockSets: ' . implode(',', $blockSets);
			}else{
				$blockSetsMessage = 'This page does not inherit any Blocks from Block Sets';
			}
			$fields->addFieldToTab('Root.Blocks', LiteralField::create('BlockSetsMessage', $blockSetsMessage));
			
			// Blocks inherited from SiteConfig
			$fields->addFieldToTab('Root.Blocks', HeaderField::create('InheritedGlobalBlocks', 'Inherited Global Blocks'));
			$fields->addFieldToTab('Root.Blocks', CheckboxField::create('InheritBlocks', 'Inherit Global Blocks from Site Configuration'));
			$excludables = $this->owner->Site()->Blocks()->map('ID', 'Title')->toArray();
			$fields->addFieldToTab('Root.Blocks', MultiValueCheckboxField::create('ExcludeInheritedBlocks', 'Exclude', $excludables));

		}else{
			$fields->addFieldToTab('Root.Blocks', LiteralField::create('Blocks', 'This page type has no Block Areas configured.'));
		}
	}


	/**
	 * Block manager for Sites (multisites module)
	 **/
	public function updateSiteCMSFields(FieldList $fields) {
		$fields->addFieldToTab('Root.GlobalBlocks', GridField::create('Blocks', 'Blocks', $this->owner->Blocks(), GridFieldConfig_BlockManager::create()));
	}


	/**
	 * Called from templates to get rendered blocks for the given area
	 * @param string $area
	 **/
	public function BlockArea($area){
		$data['BlockList'] = $this->getAllPublishedBlocks($area);
		return $this->owner->customise($data)->renderWith(array("BlockArea_$area", "BlockArea"));
	}


	/**
	 * Get a merged list of all blocks on this page and ones inherited from SiteConfig, BlockSets etc 
	 * @return ArraList
	 **/
	public function getAllPublishedBlocks($area = null){
		// get blocks directly linked to this page
		$blocks = $this->getPublishedBlocks();
		if($area) $blocks = $blocks->filter('Area', $area);
		$blocks = ArrayList::create($blocks->toArray());

		// get blocks inherited from SiteConfig
		if($this->owner->InheritBlocks){
			$inheritedBlocks = $this->owner->Site()->getPublishedBlocks()->exclude('ID', $this->owner->ExcludeInheritedBlocks->getValue());
	
			if($area) $inheritedBlocks = $inheritedBlocks->filter('Area', $area);
			$inheritedBlocks = ArrayList::create($inheritedBlocks->toArray());

			// merge inherited sources
			foreach ($inheritedBlocks as $inheritedBlock) {
				if(!$blocks->find('ID', $inheritedBlock->ID)) $blocks->unshift($inheritedBlock);
			}
		}

		// get blocks from BlockSets
		if($blocksFromSets = $this->getBlocksFromAppliedBlockSets($area)){
			// merge set sources
			foreach ($blocksFromSets as $blocksFromSet) {
				if(!$blocks->find('ID', $blocksFromSet->ID)) $blocks->unshift($blocksFromSet);
			}
		}

		return $blocks->sort('Weight');
	}


	/**
	 * Get Blocks that are directly related to this page that are published
	 * @return DataList
	 **/
	public function getPublishedBlocks(){
		return $this->owner->Blocks()->filter('Published', 1);
	}


	/**
	 * Get Any BlockSets that apply to this page 
	 * @return DataList
	 **/
	public function getAppliedSets(){
		return BlockSet::get()->filter('PageTypesValue:partialMatch', sprintf(':"%s"', $this->owner->ClassName));
	}


	/**
	 * Get all Blocks from BlockSets that apply to this page 
	 * @return ArrayList
	 **/
	public function getBlocksFromAppliedBlockSets($area = null){
		$sets = $this->getAppliedSets();

		if(!$sets) return;

		$blocks = ArrayList::create();
		foreach ($sets as $set) {
			$setBlocks = $set->getPublishedBlocks();
			if($area) {
				$setBlocks = $setBlocks->filter('Area', $area);
			}
			$blocks->merge($setBlocks);
		}
		$blocks->removeDuplicates();

		if($blocks->count() == 0) return;

		return $blocks;
	}




}