<?php
/**
 * @package framework
 * @subpackage tests
 */
class BlockTest extends SapphireTest {
	
	/**
	 * Can only test that Block is not broken
	 * {@link Block::getCMSFields}
	 */
	public function testGetCMSFields() {
		$block = new Block();
		$this->assertNotNull($block->getCMSFields());
	}
}
