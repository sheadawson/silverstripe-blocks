<?php

namespace SheaDawson\Blocks\Test;

use SheaDawson\Blocks\Extensions\BlockSiteConfigExtension;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\SiteConfig\SiteConfig;

class BlockSiteConfigExtensionTest extends SapphireTest
{
    /**
     *
     */
    public function testUpdateCMSFields()
    {
        $object = SiteConfig::current_site_config();
        $fields = $object->getCMSFields();
        $extension = new BlockSiteConfigExtension();
        $extension->updateCMSFields($fields);

        $this->assertInstanceOf(FieldList::class, $fields);
        $this->assertNull($fields->dataFieldByName('Blocks'));
    }
}