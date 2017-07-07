<?php

namespace SheaDawson\Blocks\Test;

use SheaDawson\Blocks\Model\Block;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Security\Member;

class BlockTest extends SapphireTest
{
    /**
     * @var string
     */
    protected static $fixture_file = 'fixtures.yml';

    /**
     *
     */
    public function testGetTypeForGridfield()
    {
        $object = $this->objFromFixture(Block::class, 'default');
        $this->assertEquals($object->getTypeForGridfield(), $object->singular_name());
    }

    /**
     *
     */
    public function testGetCMSFields()
    {
        $object = $this->objFromFixture(Block::class, 'default');
        $fields = $object->getCMSFields();
        $this->assertInstanceOf(FieldList::class, $fields);
    }

    /**
     *
     */
    public function testCanView()
    {
        $object = $this->objFromFixture(Block::class, 'default');
        $admin = $this->objFromFixture(Member::class, 'admin');
        $this->assertTrue($object->canView($admin));
        $member = $this->objFromFixture(Member::class, 'default');
        $this->assertTrue($object->canView($member));
    }

    /**
     *
     */
    public function testCanEdit()
    {
        $object = $this->objFromFixture(Block::class, 'default');
        $admin = $this->objFromFixture(Member::class, 'admin');
        $this->assertTrue($object->canEdit($admin));
        $member = $this->objFromFixture(Member::class, 'default');
        $this->assertFalse($object->canEdit($member));
    }

    /**
     *
     */
    public function testCanDelete()
    {
        $object = $this->objFromFixture(Block::class, 'default');
        $admin = $this->objFromFixture(Member::class, 'admin');
        $this->assertTrue($object->canDelete($admin));
        $member = $this->objFromFixture(Member::class, 'default');
        $this->assertFalse($object->canDelete($member));
    }

    /**
     *
     */
    public function testCanCreate()
    {
        $object = $this->objFromFixture(Block::class, 'default');
        $admin = $this->objFromFixture(Member::class, 'admin');
        $this->assertTrue($object->canCreate($admin));
        $member = $this->objFromFixture(Member::class, 'default');
        $this->assertFalse($object->canCreate($member));
    }

    /**
     *
     */
    public function testProvidePermissions()
    {
        $object = singleton(Block::class);
        $expected = [
            'BLOCK_EDIT' => [
                'name' => _t('Block.EditBlock', 'Edit a Block'),
                'category' => _t('Block.PermissionCategory', 'Blocks'),
            ],
            'BLOCK_DELETE' => [
                'name' => _t('Block.DeleteBlock', 'Delete a Block'),
                'category' => _t('Block.PermissionCategory', 'Blocks'),
            ],
            'BLOCK_CREATE' => [
                'name' => _t('Block.CreateBlock', 'Create a Block'),
                'category' => _t('Block.PermissionCategory', 'Blocks'),
            ],
            'BLOCK_PUBLISH' => [
                'name' => _t('Block.PublishBlock', 'Publish a Block'),
                'category' => _t('Block.PermissionCategory', 'Blocks'),
            ],
        ];

        $this->assertEquals($expected, $object->providePermissions());
    }
}