<?php

class BlocksSiteTreeExtension extends SiteTreeExtension{

	private static $many_many = array(
		'Blocks' => 'Block'
	);

	public function updateCMSFields(FieldList $fields) {
		$fields->addFieldToTab('Root.Blocks', GridField::create('Blocks', 'Blocks', $this->owner->Blocks(), GridFieldConfig_RelationEditor::create()));
	}

	public function BlockArea($area){
		$data['BlockList'] = $this->owner->Blocks()
			->filter(array(
				'Area' => $area,
				'Published' => 1
			));

		return $this->owner->customise($data)->renderWith(array("BlockArea_$area", "BlockArea"));
	}
}