<?php

namespace SheaDawson\Blocks\Model;

use SheaDawson\Blocks\Forms\GridFieldConfigBlockManager;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ArrayLib;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Security\PermissionProvider;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Group;
use SilverStripe\Forms\TreeMultiselectField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use Symbiote\MultiValueField\Fields\MultiValueCheckboxField;
use Symbiote\MultiValueField\ORM\FieldType\MultiValueField;

/**
 * BlockSet.
 *
 * @author Shea Dawson <shea@silverstripe.com.au>
 */
class BlockSet extends DataObject implements PermissionProvider
{
    /**
     * @var array
     **/
    private static $table_name = "BlockSet";

    /**
     * @var array
     **/
    private static $db = [
        'Title' => 'Varchar(255)',
        'PageTypes' => MultiValueField::class,
        'IncludePageParent' => 'Boolean',
    ];

    /**
     * @var array
     **/
    private static $many_many = [
        "Blocks" => Block::class,
        "PageParents" => SiteTree::class,
    ];

    /**
     * @var array
     **/
    private static $many_many_extraFields = [
        'Blocks' => [
            'Sort' => 'Int',
            'BlockArea' => 'Varchar',
            'AboveOrBelow' => 'Varchar',
        ],
    ];

    /**
     * @var array
     **/
    private static $above_or_below_options = [
        'Above' => 'Above Page Blocks',
        'Below' => 'Below Page Blocks',
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeFieldFromTab('Root', 'PageParents');

        $fields->addFieldToTab('Root.Main', HeaderField::create('SettingsHeading', _t('BlockSet.Settings', 'Settings')), 'Title');
        $fields->addFieldToTab('Root.Main', MultiValueCheckboxField::create('PageTypes', _t('BlockSet.OnlyApplyToThesePageTypes', 'Only apply to these Page Types:'), $this->pageTypeOptions())
                ->setDescription(_t('BlockSet.OnlyApplyToThesePageTypesDescription', 'Selected Page Types will inherit this Block Set automatically. Leave all unchecked to apply to all page types.')));
        $fields->addFieldToTab('Root.Main', TreeMultiselectField::create('PageParents', _t('BlockSet.OnlyApplyToChildrenOfThesePages', 'Only apply to children of these Pages:'), 'SilverStripe\\CMS\\Model\\SiteTree'));
        $fields->addFieldToTab('Root.Main', CheckboxField::create('IncludePageParent', _t('BlockSet.ApplyBlockSetToSelectedPageParentsAsWellAsChildren','Apply block set to selected page parents as well as children')));

        if (!$this->ID) {
            $fields->addFieldToTab('Root.Main', LiteralField::create('NotSaved', "<p class='message warning'>"._t('BlockSet.YouCanAddBlocksToThisSetOnceYouHaveSavedIt', 'You can add Blocks to this set once you have saved it for the first time').'</p>'));

            return $fields;
        }

        $fields->removeFieldFromTab('Root', 'Blocks');

        /**
        * @todo - change relation editor back to the custom block manager config and fix issues when 'creating' Blocks from a BlockSet.
		*/
        $gridConfig = GridFieldConfig_RelationEditor::create();
        $gridConfig->addComponent(new GridFieldOrderableRows('Sort'));
        $gridConfig->addComponent(new GridFieldDeleteAction());

        $gridSource = $this->Blocks()->Sort('Sort');

        $fields->addFieldToTab('Root.Blocks', HeaderField::create('BlocksHeading', _t('Block.PLURALNAME', 'Blocks')));
        $fields->addFieldToTab('Root.Blocks', GridField::create('Blocks', _t('Block.PLURALNAME', 'Blocks'), $gridSource, $gridConfig));

        return $fields;
    }

    /**
     * Returns a sorted array suitable for a dropdown with pagetypes and their translated name.
     *
     * @return array
     */
    protected function pageTypeOptions()
    {
        $pageTypes = [];
        $classes = ArrayLib::valueKey(SiteTree::page_type_classes());
        unset($classes['VirtualPage']);
        unset($classes['ErrorPage']);
        unset($classes['RedirectorPage']);
        foreach ($classes as $pageTypeClass) {
            $pageTypes[$pageTypeClass] = singleton($pageTypeClass)->i18n_singular_name();
        }
        asort($pageTypes);

        return $pageTypes;
    }

    /**
     * Returns a list of pages this BlockSet features on.
     *
     * @return DataList
     */
    public function Pages()
    {
        $pages = SiteTree::get();
        $types = $this->PageTypes->getValue();
        if (count($types)) {
            $pages = $pages->filter('ClassName', $types);
        }

        $parents = $this->PageParents()->column('ID');
        if (count($parents)) {
            $pages = $pages->filter('ParentID', $parents);
        }

        return $pages;
    }

    public function canView($member = null)
    {
        return true;
    }

    public function canEdit($member = null)
    {
        return Permission::check('ADMIN', 'any', $member) || Permission::check('BLOCK_EDIT', 'any', $member);
    }

    public function canDelete($member = null)
    {
        return Permission::check('ADMIN', 'any', $member) || Permission::check('BLOCK_DELETE', 'any', $member);
    }

    public function canCreate($member = null, $context = [])
    {
        return Permission::check('ADMIN', 'any', $member) || Permission::check('BLOCK_CREATE', 'any', $member);
    }

    public function providePermissions()
    {
        return [
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
    }
}
