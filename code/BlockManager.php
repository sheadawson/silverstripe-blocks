<?php
/**
 * BlockManager.
 *
 * @author Shea Dawson <shea@livesource.co.nz>
 */
class BlockManager extends Object
{
    /**
     * Define areas and config on a per theme basis.
     *
     * @var array
     **/
    private static $themes = array();

    /**
     * Use default ContentBlock class.
     *
     * @var bool
     **/
    private static $use_default_blocks = true;

    /**
     * Show a block area preview button in CMS
     *
     * @var bool
     **/
    private static $block_area_preview = true;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Gets an array of all areas defined for the current theme.
     *
     * @param string $theme
     * @param bool   $keyAsValue
     *
     * @return array $areas
     **/
    public function getAreasForTheme($theme = null, $keyAsValue = true)
    {
        $theme = $theme ? $theme : $this->getTheme();
        if (!$theme) {
            return false;
        }
        $config = $this->config()->get('themes');
        if (!isset($config[$theme]['areas'])) {
            return false;
        }
        $areas = $config[$theme]['areas'];
        $areas = $keyAsValue ? ArrayLib::valuekey(array_keys($areas)) : $areas;
        if (count($areas)) {
            foreach ($areas as $k => $v) {
                $areas[$k] = $keyAsValue ? FormField::name_to_label($k) : $v;
            }
        }

        return $areas;
    }

    /**
     * Gets an array of all areas defined for the current theme that are compatible
     * with pages of type $class.
     *
     * @param string $class
     *
     * @return array $areas
     **/
    public function getAreasForPageType($class)
    {
        $areas = $this->getAreasForTheme(null, false);

        if (!$areas) {
            return false;
        }

        foreach ($areas as $area => $config) {
            if (!is_array($config)) {
                continue;
            }

            if (isset($config['except'])) {
                $except = $config['except'];
                if (is_array($except)
                    ? in_array($class, $except)
                    : $except == $class
                ) {
                    unset($areas[$area]);
                    continue;
                }
            }

            if (isset($config['only'])) {
                $only = $config['only'];
                if (is_array($only)
                    ? !in_array($class, $only)
                    : $only != $class
                ) {
                    unset($areas[$area]);
                    continue;
                }
            }
        }

        if (count($areas)) {
            foreach ($areas as $k => $v) {
                $areas[$k] = FormField::name_to_label($k);
            }

            return $areas;
        } else {
            return $areas;
        }
    }

    public function getBlockClasses()
    {
        $classes = ArrayLib::valuekey(ClassInfo::subclassesFor('Block'));
        array_shift($classes);
        foreach ($classes as $k => $v) {
            $classes[$k] = singleton($k)->singular_name();
        }

        if (!Config::inst()->get('BlockManager', 'use_default_blocks')) {
            unset($classes['ContentBlock']);
        }

        if ($disabledArr = Config::inst()->get('BlockManager', 'disabled_blocks')) {
            foreach ($disabledArr as $k => $v) {
                unset($classes[$v]);
            }
        }

        return $classes;
    }

    /*
     * Get the current/active theme or 'default' to support theme-less sites
     */
    private function getTheme()
    {
        $currentTheme = Config::inst()->get('SSViewer', 'theme');

        // check directly on SiteConfig incase ContentController hasn't set
        // the theme yet in ContentController->init()
        if (!$currentTheme && class_exists('SiteConfig')) {
            $currentTheme = SiteConfig::current_site_config()->Theme;
        }

        return $currentTheme ? $currentTheme : 'default';
    }

    /*
     * Get the block config for the current theme
     */
    private function getThemeConfig()
    {
        $theme = $this->getTheme();
        $config = $this->config()->get('themes');

        return $theme && isset($config[$theme]) ? $config[$theme] : null;
    }

    /*
     * Usage of BlockSets configurable from yaml
     */
    public function getUseBlockSets()
    {
        $config = $this->getThemeConfig();

        return isset($config['use_blocksets']) ? $config['use_blocksets'] : true;
    }

    /*
     * Exclusion of blocks from page types defined in yaml
     */
    public function getExcludeFromPageTypes()
    {
        $config = $this->getThemeConfig();

        return isset($config['exclude_from_page_types']) ? $config['exclude_from_page_types'] : array();
    }
    
    /*
     * Inclusion of blocks in page types deine in yaml
     */
    public function getIncludeOnlyInPageTypes()
    {
        $config = $this->getThemeConfig();

        return isset($config['include_only_in_page_types']) ? $config['include_only_in_page_types'] : array();
    }

    /*
     * Usage of extra css classes configurable from yaml
     */
    public function getUseExtraCSSClasses()
    {
        $config = $this->getThemeConfig();

        return isset($config['use_extra_css_classes']) ? $config['use_extra_css_classes'] : false;
    }

    /*
     * Prefix for the default CSSClasses
     */
    public function getPrefixDefaultCSSClasses()
    {
        $config = $this->getThemeConfig();

        return isset($config['prefix_default_css_classes']) ? $config['prefix_default_css_classes'] : false;
    }
}
