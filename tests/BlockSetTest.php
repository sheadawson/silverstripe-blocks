<?php

namespace SheaDawson\Blocks\Test;

use SheaDawson\Blocks\Model\BlockSet;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Security\Member;

class BlockSetTest extends SapphireTest
{
    /**
     * @var string
     */
    protected static $fixture_file = 'fixtures.yml';

    /**
     *
     */
    public function testGetCMSFields()
    {
        $object = $this->objFromFixture(BlockSet::class, 'default');
        $fields = $object->getCMSFields();
        $this->assertInstanceOf(FieldList::class, $fields);
    }

    /**
     *
     */
    public function testCanView()
    {
        $object = $this->objFromFixture(BlockSet::class, 'default');
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
        $object = $this->objFromFixture(BlockSet::class, 'default');
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
        $object = $this->objFromFixture(BlockSet::class, 'default');
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
        $object = $this->objFromFixture(BlockSet::class, 'default');
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
        $object = singleton(BlockSet::class);
        $expected = [
            'BLOCKSET_EDIT' => [
                'name' => _t('BlockSet.EditBlockSet','Edit a Block Set'),
                'category' => _t('Block.PermissionCategory', 'Blocks'),
            ],
            'BLOCKSET_DELETE' => [
                'name' => _t('BlockSet.DeleteBlockSet','Delete a Block Set'),
                'category' => _t('Block.PermissionCategory', 'Blocks'),
            ],
            'BLOCKSET_CREATE' => [
                'name' => _t('BlockSet.CreateBlockSet','Create a Block Set'),
                'category' => _t('Block.PermissionCategory', 'Blocks'),
            ],
        ];

        $this->assertEquals($expected, $object->providePermissions());
    }
}