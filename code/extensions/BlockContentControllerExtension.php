<?php
class BlocksContentControllerExtension extends Extension {

	/**
	 * @var array
	 */
	private static $allowed_actions = array(
		'handleBlock'
	);

	public function onAfterInit(){
		if($this->owner->getRequest()->getVar('block_preview') == 1){
			Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
			Requirements::javascript(BLOCK_PATH . '/javascript/block-preview.js');
			Requirements::css(BLOCK_PATH . '/css/block-preview.css');
		}
	}

	/**
	 * Handles blocks attached to a page
	 * Assumes URLs in the following format: <URLSegment>/block/<block-ID>.
	 * 
	 * @return RequestHandler
	 */
	public function handleBlock() {
		if($id = $this->owner->getRequest()->param('ID')){
			$blocks = $this->owner->data()->getBlockList(null, true, true, true);
			if($block = $blocks->find('ID', $id)){
				return $block->getController();
			}
		}
		return $block->getController();
	}

}