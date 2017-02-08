<?php

namespace SheaDawson\Blocks\forms;

use SheaDawson\Blocks\model\Block;
use SheaDawson\Blocks\model\BlockSet;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldCopyButton;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\GridFieldExtensions\GridFieldAddNewMultiClass;
use SilverStripe\GridFieldExtensions\GridFieldAddExistingSearchButton;
use SilverStripe\GridFieldExtensions\GridFieldEditableColumns;

/**
 * GridFieldConfig_BlockManager
 * Provides a reusable GridFieldConfig for managing Blocks.
 *
 * @author Shea Dawson <shea@livesource.co.nz>
 */
class GridFieldConfigBlockManager extends GridFieldConfig
{
    public $blockManager;

    public function __construct($canAdd = true, $canEdit = true, $canDelete = true, $editableRows = false, $aboveOrBelow = false)
    {
        parent::__construct();

        $this->blockManager = Injector::inst()->get("SheaDawson\Blocks\BlockManager");
        $controllerClass = Controller::curr()->class;
        // Get available Areas (for page) or all in case of ModelAdmin
        if ($controllerClass == 'CMSPageEditController') {
            $currentPage = Controller::curr()->currentPage();
            $areasFieldSource = $this->blockManager->getAreasForPageType($currentPage->ClassName);
        } else {
            $areasFieldSource = $this->blockManager->getAreasForTheme();
        }

        // EditableColumns only makes sense on Saveable parenst (eg Page), or inline changes won't be saved
        if ($editableRows) {
            $this->addComponent($editable = new GridFieldEditableColumns());
            $displayfields = array(
                'TypeForGridfield' => array('title' => _t('Block.BlockType', 'Block Type'), 'field' => 'SilverStripe\\Forms\\LiteralField'),
                'Title' => array('title' => _t('Block.Title', 'Title'), 'field' => 'Silverstripe\\Forms\\ReadonlyField'),
                'BlockArea' => array(
                    'title' => _t('Block.BlockArea', 'Block Area'),
                    'callback' => function () use ($areasFieldSource) {
                            $areasField = DropdownField::create('BlockArea', 'Block Area', $areasFieldSource);
                            if (count($areasFieldSource) > 1) {
                                $areasField->setHasEmptyDefault(true);
                            }
                            return $areasField;
                        },
                ),
                'isPublishedIcon' => array('title' => _t('Block.IsPublishedField', 'Published'), 'field' => 'SilverStripe\\Forms\\LiteralField'),
                'UsageListAsString' => array('title' => _t('Block.UsageListAsString', 'Used on'), 'field' => 'SilverStripe\\Forms\\LiteralField'),
            );

            if ($aboveOrBelow) {
                $displayfields['AboveOrBelow'] = array(
                    'title' => _t('GridFieldConfigBlockManager.AboveOrBelow', 'Above or Below'),
                    'callback' => function () {
                        return DropdownField::create('AboveOrBelow', _t('GridFieldConfigBlockManager.AboveOrBelow', 'Above or Below'), BlockSet::config()->get('above_or_below_options'));
                    },
                );
            }
            $editable->setDisplayFields($displayfields);
        } else {
            $this->addComponent($dcols = new GridFieldDataColumns());

            $displayfields = array(
                'TypeForGridfield' => array('title' => _t('Block.BlockType', 'Block Type'), 'field' => 'SilverStripe\\Forms\\LiteralField'),
                'Title' => _t('Block.Title', 'Title'),
                'BlockArea' => _t('Block.BlockArea', 'Block Area'),
                'isPublishedIcon' => array('title' => _t('Block.IsPublishedField', 'Published'), 'field' => 'SilverStripe\\Forms\\LiteralField'),
                'UsageListAsString' => _t('Block.UsageListAsString', 'Used on'),
            );
            $dcols->setDisplayFields($displayfields);
            $dcols->setFieldCasting(array('UsageListAsString' => 'HTMLText->Raw'));
        }


        $this->addComponent(new GridFieldButtonRow('before'));
        $this->addComponent(new GridFieldToolbarHeader());
        $this->addComponent(new GridFieldDetailForm());
        $this->addComponent($sort = new GridFieldSortableHeader());
        $this->addComponent($filter = new GridFieldFilterHeader());
        $this->addComponent(new GridFieldDetailForm());
        if ($controllerClass == 'BlockAdmin' && class_exists('GridFieldCopyButton')) {
            $this->addComponent(new GridFieldCopyButton());
        }

        $filter->setThrowExceptionOnBadDataType(false);
        $sort->setThrowExceptionOnBadDataType(false);

        if ($canAdd) {
            $multiClass = new GridFieldAddNewMultiClass();
            $classes = $this->blockManager->getBlockClasses();
            $multiClass->setClasses($classes);
            $this->addComponent($multiClass);
            //$this->addComponent(new GridFieldAddNewButton());
        }

        if ($canEdit) {
            $this->addComponent(new GridFieldEditButton());
        }

        if ($canDelete) {
            $this->addComponent(new GridFieldDeleteAction(true));
        }

        return $this;
    }

    /**
     * Add the GridFieldAddExistingSearchButton component to this grid config.
     *
     * @return $this
     **/
    public function addExisting()
    {
        $classes = $this->blockManager->getBlockClasses();

        $this->addComponent($add = new GridFieldAddExistingSearchButton());
        $add->setSearchList(Block::get()->filter(array(
            'ClassName' => array_keys($classes),
        )));

        return $this;
    }

    /**
     * Add the GridFieldBulkManager component to this grid config.
     *
     * @return $this
     **/
    public function addBulkEditing()
    {
        if (class_exists('GridFieldBulkManager')) {
            $this->addComponent(new GridFieldBulkManager());
        }

        return $this;
    }
}
