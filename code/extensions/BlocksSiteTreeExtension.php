<?php

/**
 * @package silverstipe blocks
 * @author Shea Dawson <shea@silverstripe.com.au>
 */
class BlocksSiteTreeExtension extends SiteTreeExtension {

	private static $db = array(
		'InheritBlockSets' => 'Boolean'
	);
	private static $many_many = array(
		'Blocks' => 'Block',
		'DisabledBlocks' => 'Block'
	);
	public static $many_many_extraFields = array(
		'Blocks' => array(
			'Sort' => 'Int',
			'BlockArea' => 'Varchar'
		)
	);
	private static $defaults = array(
		'InheritBlockSets' => 1
	);
	private static $dependencies = array(
		'blockManager' => '%$blockManager',
	);
	public $blockManager;

	/**
	 * Block manager for Pages
	 * */
	public function updateCMSFields(FieldList $fields) {
		if($fields->fieldByName('Root.Blocks') || in_array($this->owner->ClassName, $this->blockManager->getExcludeFromPageTypes())){
			return;
		}

		$areas = $this->blockManager->getAreasForPageType($this->owner->ClassName);

		if ($areas && count($areas)) {
			$fields->addFieldToTab('Root.Blocks', 
					LiteralField::create('PreviewLink', $this->areasPreviewButton()));

			// Blocks related directly to this Page 
			$gridConfig = GridFieldConfig_BlockManager::create(true, true, true, true)
				->addExisting($this->owner->class)
				->addBulkEditing()
				->addComponent(new GridFieldOrderableRows())
				;

			// TODO it seems this sort is not being applied...
			$gridSource = $this->owner->Blocks();
				// ->sort(array(
				// 	"FIELD(SiteTree_Blocks.BlockArea, '" . implode("','", array_keys($areas)) . "')" => '',
				// 	'SiteTree_Blocks.Sort' => 'ASC', 
				// 	'Name' => 'ASC'
				// ));

			$fields->addFieldToTab('Root.Blocks', GridField::create('Blocks', 'Blocks', $gridSource, $gridConfig));


			// Blocks inherited from BlockSets
			$blocksetsactive = $this->blockManager->getUseBlockSets();
			if($blocksetsactive){
				
				$fields->addFieldToTab('Root.Blocks', 
						HeaderField::create('InheritedBlocksHeader', 'Blocks inherited from Block Sets'));
				
				$fields->addFieldToTab('Root.Blocks', 
					CheckboxField::create('InheritBlockSets', 'Inherit Blocks from Block Sets'));
				
				$allInherited = $this->getBlockList(
						$area = null, $publishedOnly = false, 
						$includeNative = false, $includeSets = true, 
						$includeDisabled = true );
			
				if ($allInherited->count()) {
					$fields->addFieldToTab('Root.Blocks', 
							ListBoxField::create('DisabledBlocks', 'Disable Inherited Blocks', 
									$allInherited->map('ID', 'Title'), null, null, true)
								->setDescription('Select any inherited blocks that you would not like displayed on this page.')
					);
					
					$activeInherited = $this->getBlocksFromAppliedBlockSets(null, false, false);

					if ($activeInherited->count()) {
						$fields->addFieldToTab('Root.Blocks', 
								GridField::create('InheritedBlockList', 'Inherited Blocks', $activeInherited, 
										GridFieldConfig_BlockManager::create(false, false, false)));
					}
				} else {
					$fields->addFieldToTab('Root.Blocks', 
							ReadonlyField::create('DisabledBlocksReadOnly', 'Disable Inherited Blocks', 
									'This page has no inherited blocks to disable.'));
				}
			}
			
		} else {
			$fields->addFieldToTab('Root.Blocks', 
					LiteralField::create('Blocks', 'This page type has no Block Areas configured.'));
		}
	}

	/**
	 * Called from templates to get rendered blocks for the given area
	 * @param string $area
	 * @param integer $limit Limit the items to this number, or null for no limit
	 */
	public function BlockArea($area, $limit = null) {
		if ($this->owner->ID <= 0) return; // blocks break on fake pages ie Security/login

		$list = $this->getBlockList($area);

		foreach ($list as $block) {
			if (!$block->canView()) {
				$list->remove($block);
			}
		}

		if ($limit !== null) {
			$list = $list->limit($limit);
		}

		$data['BlockArea'] = $list;
		$data['AreaID'] = $area;

		$data = $this->owner->customise($data);

		$template[] = 'BlockArea_' . $area;
		if (SSViewer::hasTemplate($template)) {
			return $data->renderWith($template);
		} else {
			return $data->renderWith('BlockArea');
		}
	}

	/**
	 * Get a merged list of all blocks on this page and ones inherited from BlockSets 
	 * 
	 * @param string|null $area filter by block area
	 * @param boolean $publishedOnly only return published blocks
	 * @param boolean $includeNative Include blocks directly assigned to this page
	 * @param boolean $includeSets Include block sets
	 * @param boolean $includeDisabled Include blocks that have been explicitly excluded from this page
	 * i.e. blocks from block sets added to the "disable inherited blocks" list
	 * @return ArrayList
	 * */
	public function getBlockList($area = null, $includeDisabled = false) {
		$includeSets = $this->blockManager->getUseBlockSets() && $this->owner->InheritBlockSets;
		$blocks = ArrayList::create();

		// get blocks directly linked to this page
		$nativeBlocks = $this->owner->Blocks()->sort('Sort');
		if ($area) {
			$nativeBlocks = $nativeBlocks->filter('BlockArea', $area);
		}

		if ($nativeBlocks->count()) {
			foreach($nativeBlocks as $block){
				$blocks->add($block);
			}
		}

		// get blocks from BlockSets
		if ($includeSets) {
			$blocksFromSets = $this->getBlocksFromAppliedBlockSets($area, $includeDisabled);
			if($blocksFromSets){
				// merge set sources
				foreach ($blocksFromSets as $block) {
					if (!$blocks->find('ID', $block->ID)) {
						$blocks->push($block);
					}
				}
			}
		}

		return $blocks;
	}


	/**
	 * Get Any BlockSets that apply to this page 
	 * @todo could be more efficient
	 * @return ArrayList
	 * */
	public function getAppliedSets() {
		$sets = BlockSet::get()->where("(PageTypesValue IS NULL) OR (PageTypesValue LIKE '%:\"{$this->owner->ClassName}%')");

		$list = ArrayList::create();
		$ancestors = $this->owner->getAncestors()->column('ID');

		foreach ($sets as $set) {
			$restrictedToParerentIDs = $set->PageParents()->column('ID');
			if (count($restrictedToParerentIDs) && count($ancestors)) {
				foreach ($ancestors as $ancestor) {
					if (in_array($ancestor, $restrictedToParerentIDs)) {
						$list->add($set);
						continue;
					}
				}
			} else {
				$list->add($set);
			}
		}
		return $list;
	}

	/**
	 * Get all Blocks from BlockSets that apply to this page 
	 * @return ArrayList
	 * */
	public function getBlocksFromAppliedBlockSets($area = null, $includeDisabled = false) {
		$sets = $this->getAppliedSets();
		if (!$sets) return false;
		$blocks = ArrayList::create();
		
		foreach ($sets as $set) {
			$setBlocks = $set->Blocks()->sort('Sort');
				
			if (!$includeDisabled) {
				$setBlocks = $setBlocks->exclude('ID', $this->owner->DisabledBlocks()->column('ID'));
			}

			if ($area) {
				$setBlocks = $setBlocks->filter('BlockArea', $area);
			}

			$blocks->merge($setBlocks);
		}
		
		$blocks->removeDuplicates();
		if ($blocks->count() == 0) return false;
		return $blocks;
	}

	/**
	 * Get's the link for a block area preview button
	 * @return string
	 * */
	public function areasPreviewLink() {
		return Controller::join_links($this->owner->Link(), '?block_preview=1');
	}

	/**
	 * Get's html for a block area preview button
	 * @return string
	 * */
	public function areasPreviewButton() {
		return "<a class='ss-ui-button ss-ui-button-small' style='font-style:normal;' href='" . $this->areasPreviewLink() . "' target='_blank'>Preview Block Areas for this page</a>";
	}

}
