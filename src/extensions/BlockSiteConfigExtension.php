<?php

namespace SheaDawson\Blocks\extensions;

use SilverStripe\ORM\DataExtension;
use SheaDawson\Blocks\model\Block;

/**
 * Legacy extension to aid with migrating from Blocks 0.x to 1.x.
 *
 * @author Shea Dawson <shea@silverstripe.com.au>
 */
class BlockSiteConfigExtension extends DataExtension
{
    private static $many_many = [
        "Blocks" => Block::class,
    ];

    /**
     *
     **/
    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName('Blocks');
    }
}
