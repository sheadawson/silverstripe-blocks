<?php
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
    private static $db = array(
        'Title' => 'Varchar(255)',
        'PageTypes' => 'MultiValueField',
        'IncludePageParent' => 'Boolean',
    );

    /**
     * @var array
     **/
    private static $many_many = array(
        'Blocks' => 'Block',
        'PageParents' => 'SiteTree',
    );

    /**
     * @var array
     **/
    private static $many_many_extraFields = array(
        'Blocks' => array(
            'Sort' => 'Int',
            'BlockArea' => 'Varchar',
            'AboveOrBelow' => 'Varchar',
        ),
    );

    /**
     * @var array
     **/
    private static $above_or_below_options = array(
        'Above' => 'Above Page Blocks',
        'Below' => 'Below Page Blocks',
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeFieldFromTab('Root', 'PageParents');

        $fields->addFieldToTab('Root.Main', HeaderField::create('SettingsHeading', 'Settings'), 'Title');
        $fields->addFieldToTab('Root.Main', MultiValueCheckboxField::create('PageTypes', 'Only apply to these Page Types:', $this->pageTypeOptions())
                ->setDescription('Selected Page Types will inherit this Block Set automatically. Leave all unchecked to apply to all page types.'));
        $fields->addFieldToTab('Root.Main', TreeMultiselectField::create('PageParents', 'Only apply to children of these Pages:', 'SiteTree'));
        $fields->addFieldToTab('Root.Main', CheckboxField::create('IncludePageParent', 'Apply block set to selected page parents as well as children'));

        if (!$this->ID) {
            $fields->addFieldToTab('Root.Main', LiteralField::create('NotSaved', "<p class='message warning'>You can add Blocks to this set once you have saved it for the first time</p>"));

            return $fields;
        }

        $fields->removeFieldFromTab('Root', 'Blocks');
        $gridConfig = GridFieldConfig_BlockManager::create(true, true, true, true, true)
            ->addExisting()
            ->addComponent(new GridFieldOrderableRows());

        $gridSource = $this->Blocks()->Sort('Sort');

        $fields->addFieldToTab('Root.Main', HeaderField::create('BlocksHeading', 'Blocks'));
        $fields->addFieldToTab('Root.Main', GridField::create('Blocks', 'Blocks', $gridSource, $gridConfig));

        return $fields;
    }

    /**
     * Returns a sorted array suitable for a dropdown with pagetypes and their translated name.
     *
     * @return array
     */
    protected function pageTypeOptions()
    {
        $pageTypes = array();
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
        return Permission::check('ADMIN') || Permission::check('BLOCK_EDIT');
    }

    public function canDelete($member = null)
    {
        return Permission::check('ADMIN') || Permission::check('BLOCK_DELETE');
    }

    public function canCreate($member = null)
    {
        return Permission::check('ADMIN') || Permission::check('BLOCK_CREATE');
    }

    public function providePermissions()
    {
        return array(
            'BLOCKSET_EDIT' => array(
                'name' => 'Edit a Block Set',
                'category' => 'Blocks',
            ),
            'BLOCKSET_DELETE' => array(
                'name' => 'Delete a Block Set',
                'category' => 'Blocks',
            ),
            'BLOCKSET_CREATE' => array(
                'name' => 'Create a Block Set',
                'category' => 'Blocks',
            ),
        );
    }
}
