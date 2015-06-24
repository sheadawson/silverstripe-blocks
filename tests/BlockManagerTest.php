<?php
/**
 * @package framework
 * @subpackage tests
 */
class BlockManagerTest extends SapphireTest {
	
	/**
	 * {@link BlockManager::getGridDisplayFields}
	 */
	public function testGetGridDisplayFields() {
		$manager = new BlockManager();
		
		// with extensions, we can only hope to receive an array
		if(count($manager->getExtensionInstances()) > 0) {
			$this->assertTrue(is_array($manager->getGridDisplayFields()));
			return;
		}
		
		// without extensions we have a base of fields to test
		$display = $manager->getGridDisplayFields();
		$this->assertArrayHasKey('singular_name', $display);
		$this->assertArrayHasKey('Title', $display);
		$this->assertArrayHasKey('BlockArea', $display);
		$this->assertArrayHasKey('isPublishedNice', $display);
		$this->assertArrayHasKey('UsageListAsString', $display);
		$this->assertArrayNotHasKey('AboveOrBelow', $display);
		
		// testing fields for editable option, should be the same as not-editable with exception of aboveOrBelow
		$display = $manager->getGridDisplayFields(null, true, true);
		$this->assertArrayHasKey('singular_name', $display);
		$this->assertArrayHasKey('Title', $display);
		$this->assertArrayHasKey('BlockArea', $display);
		$this->assertArrayHasKey('isPublishedNice', $display);
		$this->assertArrayHasKey('UsageListAsString', $display);
		$this->assertArrayHasKey('AboveOrBelow', $display);
	}
}
