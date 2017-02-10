<?php

namespace SheaDawson\Blocks\Extensions;

use SheaDawson\Blocks\Model\Block;
use SilverStripe\ORM\DataExtension;

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
