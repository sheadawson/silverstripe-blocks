<?php

namespace Blocks\Extensions;

use SilverStripe\ORM\DataExtension;

/**
 * Legacy extension to aid with migrating from Blocks 0.x to 1.x.
 *
 * @author Shea Dawson <shea@silverstripe.com.au>
 */
class BlockSiteConfigExtension extends DataExtension
{
    private static $many_many = array(
        'Blocks' => 'Block',
    );

    /**
     *
     **/
    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName('Blocks');
    }
}
