<?php

class ContentBlock extends Block{

	private static $singular_name = 'Content Block';
	private static $plural_name = 'Content Blocks';

	private static $db = array(
		'Content' => 'HTMLText'
	);


	public function getCMSFields(){
		$fields = parent::getCMSFields();

		return $fields;
	}

}