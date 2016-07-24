<?php
/**
 * Block
 * Subclass this basic Block with your more interesting ones.
 *
 * @author Shea Dawson <shea@silverstripe.com.au>
 */
class Block extends DataObject implements PermissionProvider
{
    /**
     * @var array
     */
    private static $db = array(
        'Title' => 'Varchar(255)',
        'CanViewType' => "Enum('Anyone, LoggedInUsers, OnlyTheseUsers', 'Anyone')",
        'ExtraCSSClasses' => 'Varchar',
        // these are legacy fields, in place to make migrations from old blocks version easier
        'Weight' => 'Int',
        'Area' => 'Varchar',
        'Published' => 'Boolean',
    );

    /**
     * @var array
     */
    private static $many_many = array(
        'ViewerGroups' => 'Group',
    );

    /**
     * @var array
     */
    private static $belongs_many_many = array(
        'Pages' => 'SiteTree',
        'BlockSets' => 'BlockSet',
    );

    private static $summary_fields = array(
        'singular_name',
        'Title',
        'isPublishedField',
        'UsageListAsString',
    );

    private static $searchable_fields = array(
            'Title',
            'ClassName',
    );

    public function fieldLabels($includerelations = true)
    {
        $labels =  parent::fieldLabels($includerelations);

        $labels = array_merge($labels, array(
            'singular_name' => _t('Block.BlockType', 'Block Type'),
            'Title' => _t('Block.Title', 'Title'),
            'isPublishedField' => _t('Block.IsPublishedField', 'Published'),
            'UsageListAsString' => _t('Block.UsageListAsString', 'Used on'),
            'ExtraCSSClasses' => _t('Block.ExtraCSSClasses', 'Extra CSS Classes'),
            'Content' => _t('Block.Content', 'Content'),
            'ClassName' => _t('Block.BlockType', 'Block Type'),
        ));

        return $labels;
    }

    public function getDefaultSearchContext()
    {
        $context = parent::getDefaultSearchContext();

        $results = $this->blockManager->getBlockClasses();
        if (sizeof($results) > 1) {
            $classfield = new DropdownField('ClassName', _t('Block.BlockType', 'Block Type'));
            $classfield->setSource($results);
            $classfield->setEmptyString(_t('Block.Any', '(any)'));
            $context->addField($classfield);
        }

        return $context;
    }

    /**
     * @var array
     */
    private static $default_sort = array('Title' => 'ASC');

    /**
     * @var array
     */
    private static $dependencies = array(
        'blockManager' => '%$blockManager',
    );

    /**
     * @var BlockManager
     */
    public $blockManager;

    /**
     * @var BlockController
     */
    protected $controller;

    /**
     * @return mixed
     */
    public function getTypeForGridfield()
    {
        return $this->singular_name();
    }

    public function getCMSFields()
    {
        Requirements::add_i18n_javascript(BLOCKS_DIR.'/javascript/lang');

        // this line is a temporary patch until I can work out why this dependency isn't being
        // loaded in some cases...
        if (!$this->blockManager) {
            $this->blockManager = singleton('BlockManager');
        }

        $fields = parent::getCMSFields();

        // ClassNmae - block type/class field
        $classes = $this->blockManager->getBlockClasses();
        $fields->addFieldToTab('Root.Main', DropdownField::create('ClassName', _t('Block.BlockType', 'Block Type'), $classes)->addExtraClass('block-type'), 'Title');

        // BlockArea - display areas field if on page edit controller
        if (Controller::curr()->class == 'CMSPageEditController') {
            $currentPage = Controller::curr()->currentPage();
            $areas = $this->blockManager->getAreasForPageType($currentPage->ClassName);
            $fields->addFieldToTab(
                'Root.Main',
                $blockAreaField = DropdownField::create('ManyMany[BlockArea]', _t('Block.BlockArea','Block Area'), $areas),
                'ClassName'
            );

            if (count($areas) > 1) {
                $blockAreaField->setEmptyString('(Select one)');
            }

            if (BlockManager::config()->get('block_area_preview')) {
                $blockAreaField->setRightTitle($currentPage->areasPreviewButton());
            }
        }

        $fields->removeFieldFromTab('Root', 'BlockSets');
        $fields->removeFieldFromTab('Root', 'Pages');

        // legacy fields, will be removed in later release
        $fields->removeByName('Weight');
        $fields->removeByName('Area');
        $fields->removeByName('Published');

        if ($this->blockManager->getUseExtraCSSClasses()) {
            $fields->addFieldToTab('Root.Main', $fields->dataFieldByName('ExtraCSSClasses'), 'Title');
        } else {
            $fields->removeByName('ExtraCSSClasses');
        }

        // Viewer groups
        $fields->removeFieldFromTab('Root', 'ViewerGroups');
        $groupsMap = Group::get()->map('ID', 'Breadcrumbs')->toArray();
        asort($groupsMap);
        $viewersOptionsField = new OptionsetField(
            'CanViewType',
            _t('SiteTree.ACCESSHEADER', 'Who can view this page?')
        );
        $viewerGroupsField = ListboxField::create('ViewerGroups', _t('SiteTree.VIEWERGROUPS', 'Viewer Groups'))
            ->setMultiple(true)
            ->setSource($groupsMap)
            ->setAttribute(
                'data-placeholder',
                _t('SiteTree.GroupPlaceholder', 'Click to select group')
        );
        $viewersOptionsSource = array();
        $viewersOptionsSource['Anyone'] = _t('SiteTree.ACCESSANYONE', 'Anyone');
        $viewersOptionsSource['LoggedInUsers'] = _t('SiteTree.ACCESSLOGGEDIN', 'Logged-in users');
        $viewersOptionsSource['OnlyTheseUsers'] = _t('SiteTree.ACCESSONLYTHESE', 'Only these people (choose from list)');
        $viewersOptionsField->setSource($viewersOptionsSource)->setValue('Anyone');

        $fields->addFieldToTab('Root', new Tab('ViewerGroups', _t('Block.ViewerGroups', 'Viewer Groups')));
        $fields->addFieldsToTab('Root.ViewerGroups', array(
            $viewersOptionsField,
            $viewerGroupsField,
        ));

        // Disabled for now, until we can list ALL pages this block is applied to (inc via sets)
        // As otherwise it could be misleading
        // Show a GridField (list only) with pages which this block is used on
        // $fields->removeFieldFromTab('Root.Pages', 'Pages');
        // $fields->addFieldsToTab('Root.Pages',
        // 		new GridField(
        // 				'Pages',
        // 				'Used on pages',
        // 				$this->Pages(),
        // 				$gconf = GridFieldConfig_Base::create()));
        // enhance gridfield with edit links to pages if GFEditSiteTreeItemButtons is available
        // a GFRecordEditor (default) combined with BetterButtons already gives the possibility to
        // edit versioned records (Pages), but STbutton loads them in their own interface instead
        // of GFdetailform
        // if(class_exists('GridFieldEditSiteTreeItemButton')) {
        // 	$gconf->addComponent(new GridFieldEditSiteTreeItemButton());
        // }

        return $fields;
    }

    /**
     * Renders this block with appropriate templates
     * looks for templates that match BlockClassName_AreaName
     * falls back to BlockClassName.
     *
     * @return string
     **/
    public function forTemplate()
    {
        if ($this->BlockArea) {
            $template = array($this->class.'_'.$this->BlockArea);

            if (SSViewer::hasTemplate($template)) {
                return $this->renderWith($template);
            }
        }

        return $this->renderWith($this->ClassName, $this->getController());
    }

    /**
     * @return string
     */
    public function BlockHTML()
    {
        return $this->forTemplate();
    }

    /*
     * Checks if deletion was an actual deletion, not just unpublish
     * If so, remove relations
     */
    public function onAfterDelete()
    {
        parent::onAfterDelete();
        if (Versioned::current_stage() == 'Stage') {
            $this->Pages()->removeAll();
            $this->BlockSets()->removeAll();
        }
    }

    /**
     * Remove relations onAfterDuplicate.
     */
    public function onAfterDuplicate()
    {
        // remove relations to pages & blocksets duplicated from the original item
        $this->Pages()->removeAll();
        $this->BlockSets()->removeAll();
    }

    /*
     * Base permissions
     */
    public function canView($member = null)
    {
        if (!$member || !(is_a($member, 'Member')) || is_numeric($member)) {
            $member = Member::currentUserID();
        }

        // admin override
        if ($member && Permission::checkMember($member, array('ADMIN', 'SITETREE_VIEW_ALL'))) {
            return true;
        }

        // Standard mechanism for accepting permission changes from extensions
        $extended = $this->extendedCan('canView', $member);
        if ($extended !== null) {
            return $extended;
        }

        // check for empty spec
        if (!$this->CanViewType || $this->CanViewType == 'Anyone') {
            return true;
        }

        // check for any logged-in users
        if ($this->CanViewType == 'LoggedInUsers' && $member) {
            return true;
        }

        // check for specific groups
        if ($member && is_numeric($member)) {
            $member = DataObject::get_by_id('Member', $member);
        }
        if ($this->CanViewType == 'OnlyTheseUsers' && $member && $member->inGroups($this->ViewerGroups())) {
            return true;
        }

        return false;
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

    public function canPublish($member = null)
    {
        return Permission::check('ADMIN') || Permission::check('BLOCK_PUBLISH');
    }

    public function providePermissions()
    {
        return array(
            'BLOCK_EDIT' => array(
                'name' => _t('Block.EditBlock', 'Edit a Block'),
                'category' => _t('Block.PermissionCategory', 'Blocks'),
            ),
            'BLOCK_DELETE' => array(
                'name' => _t('Block.DeleteBlock', 'Delete a Block'),
                'category' => _t('Block.PermissionCategory', 'Blocks'),
            ),
            'BLOCK_CREATE' => array(
                'name' => _t('Block.CreateBlock', 'Create a Block'),
                'category' => _t('Block.PermissionCategory', 'Blocks'),
            ),
            'BLOCK_PUBLISH' => array(
                'name' => _t('Block.PublishBlock', 'Publish a Block'),
                'category' => _t('Block.PermissionCategory', 'Blocks'),
            ),
        );
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();
        if ($this->hasExtension('FilesystemPublisher')) {
            $this->republish($this);
        }
    }

    /**
     * Get a list of URL's to republish when this block changes
     * if using StaticPublisher module.
     */
    public function pagesAffectedByChanges()
    {
        $pages = $this->Pages();
        $urls = array();
        foreach ($pages as $page) {
            $urls[] = $page->Link();
        }

        return $urls;
    }

    /*
     * Get a list of Page and Blockset titles this block is used on
     */
    public function UsageListAsString()
    {
        $pages = implode(', ', $this->Pages()->column('URLSegment'));
        $sets = implode(', ', $this->BlockSets()->column('Title'));
        $_t_pages = _t('Block.PagesAsString', 'Pages: {pages}', '', array('pages' => $pages));
        $_t_sets = _t('Block.BlockSetsAsString', 'Block Sets: {sets}', '', array('sets' => $sets));
        if ($pages && $sets) {
            return "$_t_pages<br />$_t_sets";
        }
        if ($pages) {
            return $_t_pages;
        }
        if ($sets) {
            return $_t_sets;
        }
    }

    /**
     * Check if this block has been published.
     *
     * @return bool True if this page has been published.
     */
    public function isPublished()
    {
        if (!$this->exists()) {
            return false;
        }

        return (DB::query("SELECT \"ID\" FROM \"Block_Live\" WHERE \"ID\" = $this->ID")->value())
            ? 1
            : 0;
    }

    /**
     * Check if this block has been published.
     *
     * @return bool True if this page has been published.
     */
    public function isPublishedNice()
    {
        $field = Boolean::create('isPublished');
        $field->setValue($this->isPublished());

        return $field->Nice();
    }

    /**
     * @return HTMLText
     */
    public function isPublishedIcon()
    {
        $obj = HTMLText::create();
        if ($this->isPublished()) {
            $obj->setValue('<img src="/framework/admin/images/alert-good.gif" />');
        } else {
            $obj->setValue('<img src="/framework/admin/images/alert-bad.gif" />');
        }
        return $obj;
    }

    /**
     * CSS Classes to apply to block element in template.
     *
     * @return string $classes
     */
    public function CSSClasses($stopAtClass = 'DataObject')
    {
        $classes = strtolower(parent::CSSClasses($stopAtClass));

        if (!empty($classes) && ($prefix = $this->blockManager->getPrefixDefaultCSSClasses())) {
            $classes = $prefix.str_replace(' ', " {$prefix}", $classes);
        }

        if ($this->blockManager->getUseExtraCSSClasses()) {
            $classes = $this->ExtraCSSClasses ? $classes." $this->ExtraCSSClasses" : $classes;
        }

        return $classes;
    }

    /**
     * Access current page scope from Block templates with $CurrentPage
     *
     * @return Controller
     */
    public function getCurrentPage()
    {
        return Controller::curr();
    }

    /**
     * @throws Exception
     *
     * @return BlockController
     */
    public function getController()
    {
        if ($this->controller) {
            return $this->controller;
        }
        foreach (array_reverse(ClassInfo::ancestry($this->class)) as $blockClass) {
            $controllerClass = "{$blockClass}_Controller";
            if (class_exists($controllerClass)) {
                break;
            }
        }
        if (!class_exists($controllerClass)) {
            throw new Exception("Could not find controller class for $this->classname");
        }
        $this->controller = Injector::inst()->create($controllerClass, $this);
        $this->controller->init();

        return $this->controller;
    }
}
