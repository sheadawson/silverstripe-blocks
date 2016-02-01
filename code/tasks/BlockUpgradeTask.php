<?php
/**
 * BlockUpgradeTask
 * Run this task to migrate from Blocks 0.x to 1.x
 * @package silverstipe blocks
 * @author Shea Dawson <shea@silverstripe.com.au>
 */
class BlockUpgradeTask extends BuildTask{
	public function run($request) {

		// update block/set titles
		// Name field has been reverted back to Title
		// DB::query("update Block set Name = Title");
		// DB::query("update BlockSet set Name = Title");

		// update block areas

		DB::query("
			update SiteTree_Blocks
			left join Block on SiteTree_Blocks.BlockID = Block.ID
			set BlockArea = Block.Area
			where BlockID = Block.ID
		");

		// update block sort

		DB::query("
			update SiteTree_Blocks
			left join Block on SiteTree_Blocks.BlockID = Block.ID
			set Sort = Block.Weight
			where BlockID = Block.ID
		");

		echo "BlockAreas, Sort updated<br />";

		// migrate global blocks

		$sc = SiteConfig::current_site_config();
		if($sc->Blocks()->Count()) {
			$set = BlockSet::get()->filter('Title', 'Global')->first();
			if(!$set) {
				$set = BlockSet::create(array(
					'Title' => 'Global'
				));
				$set->write();
			}
			foreach ($sc->Blocks() as $block) {
				if(!$set->Blocks()->find('ID', $block->ID)) {
					$set->Blocks()->add($block, array(
						'Sort' => $block->Weight,
						'BlockArea' => $block->Area
					));
					echo "Block #$block->ID added to Global block set<br />";
				}
			}
		}

		// publish blocks

		$blocks = Block::get()->filter('Published', 1);
		foreach ($blocks as $block) {
			$block->publish('Stage', 'Live');
			echo "Published Block #$block->ID<br />";
		}
	}
}