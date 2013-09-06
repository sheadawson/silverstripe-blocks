<?php
class BlocksContentControllerExtension extends Extension {

	public function onAfterInit(){
		if($this->owner->getRequest()->getVar('block_preview') == 1){
			Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
			Requirements::javascript(BLOCK_PATH . '/javascript/block-preview.js');
			Requirements::css(BLOCK_PATH . '/css/block-preview.css');
		}
	}
}