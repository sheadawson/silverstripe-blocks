<?php

use SheaDawson\Blocks\Model\ContentBlock;
use SilverStripe\Dev\SapphireTest;

class ContentBlockTest extends SapphireTest
{
    /**
     * @var string
     */
    protected static $fixture_file = 'fixtures.yml';

    /**
     *
     */
    public function testGetPluralName()
    {
        $object = singleton(ContentBlock::class);
        $this->assertEquals('Content Blocks', $object->plural_name());
    }
}