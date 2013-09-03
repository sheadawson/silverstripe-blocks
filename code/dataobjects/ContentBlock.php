<?php

class ContentBlock extends Block{

	private static $db = array(
		'Content' => 'HTMLText'
	);


	public function getCMSFields(){
		$fields = parent::getCMSFields();

		return $fields;
	}

}