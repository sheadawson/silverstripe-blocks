<?php
/**
 * Block_Controller.
 *
 * @author Shea Dawson <shea@silverstripe.com.au>
 */
class Block_Controller extends Controller
{
    /**
     * @var Block
     */
    protected $block;

    /**
     * @param Block $block
     */
    public function __construct($block = null)
    {
        if ($block) {
            $this->block = $block;
            $this->failover = $block;
        }

        parent::__construct();
    }

    public function index()
    {
        return;
    }

    /**
     * @param string $action
     *
     * @return string
     */
    public function Link($action = null)
    {
        $id = ($this->block) ? $this->block->ID : null;
        $segment = Controller::join_links('block', $id, $action);

        if ($page = Director::get_current_page()) {
            return $page->Link($segment);
        }

        return Controller::curr()->Link($segment);
    }

    /**
     * @return string - link to page this block is on
     */
    public function pageLink()
    {
        $parts = explode('/block/', $this->Link());

        return isset($parts[0]) ? $parts[0] : null;
    }

    /**
     * @return Block
     */
    public function getBlock()
    {
        return $this->block;
    }

    /**
     * CSS Classes to apply to block element in template.
     *
     * @return string $classes
     */
    public function CSSClasses($stopAtClass = 'DataObject')
    {
        return $this->getBlock()->CSSClasses($stopAtClass);
    }
}
