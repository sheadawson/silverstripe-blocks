<?php
/**
 * BlockManager
 * @package silverstipe blocks
 * @author Shea Dawson <shea@livesource.co.nz>
 */
class BlockManager extends Object{

	/**
	 * Define areas and config on a per theme basis
	 * @var array
	 **/
	private static $themes = array();


	/**
	 * Use default ContentBlock class
	 * @var Boolean
	 **/
	private static $use_default_blocks = true;


	public function __construct(){
		parent::__construct();
	}
	
	/**
	 * Gets an array of all areas defined for the current theme
	 * @param string $theme
	 * @param bool $keyAsValue
	 * @return array $areas
	 **/
	public function getAreasForTheme($theme = null, $keyAsValue = true){
		$theme 	= $theme ? $theme : $this->getTheme();
		if(!$theme){
			return false;
		}
		$config = $this->config()->get('themes');
		if(!isset($config[$theme]['areas'])){
			return false;
		}
		$areas 	= $config[$theme]['areas'];
		$areas = $keyAsValue ? ArrayLib::valuekey(array_keys($areas)) : $areas;
		if(count($areas)){
			foreach ($areas as $k => $v) {
				$areas[$k] = $keyAsValue ? FormField::name_to_label($k) : $v;
			}	
		}
		return $areas;
		
	}


	/**
	 * Gets an array of all areas defined for the current theme that are compatible
	 * with pages of type $class
	 * @param string $class
	 * @return array $areas
	 **/
	public function getAreasForPageType($class){
		$areas = $this->getAreasForTheme(null, false);

		if(!$areas){
			return false;
		}

		foreach($areas as $area => $config) {
			if(!is_array($config)) {
				continue;
			}

			if(isset($config['except'])) {
				$except = $config['except'];
				if (is_array($except)
					? in_array($class, $except)
					: $except == $class
				) {
					unset($areas[$area]);
					continue;
				}
			}

			if(isset($config['only'])) {
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
		
		if(count($areas)){
			foreach ($areas as $k => $v) {
				$areas[$k] = FormField::name_to_label($k);
			}
			return $areas;
		}else{
			return $areas;
		}
	}


	public function getBlockClasses(){
		$classes = ArrayLib::valuekey(ClassInfo::subclassesFor('Block'));
		array_shift($classes);
		foreach ($classes as $k => $v) {
			$classes[$k] = singleton($k)->singular_name();
		}

		if(!Config::inst()->get('BlockManager', 'use_default_blocks')){
			unset($classes['ContentBlock']);
		}

		return $classes;
	}

	/*
     * Get the current/active theme or 'default' to support theme-less sites
     */
    private function getTheme(){
        $currentTheme = Config::inst()->get('SSViewer', 'theme');

        // check directly on SiteConfig incase ContentController hasn't set
        // the theme yet in ContentController->init()
        if(!$currentTheme && class_exists('SiteConfig')){
    		$currentTheme = SiteConfig::current_site_config()->Theme;
        }

        return $currentTheme ? $currentTheme : 'default';
    }

	/*
	 * Get the block config for the current theme
	 */
	private function getThemeConfig(){
		$theme = $this->getTheme();
		$config = $this->config()->get('themes');
		return $theme && isset($config[$theme]) ? $config[$theme] : null;
	}
	
	/*
	 * Usage of BlockSets configurable from yaml
	 */
	public function getUseBlockSets(){
		$config = $this->getThemeConfig();
		return isset($config['use_blocksets']) ? $config['use_blocksets'] : true;
	}

	/*
	 * Exclusion of blocks from page types defined in yaml
	 */
	public function getExcludeFromPageTypes(){
		$config = $this->getThemeConfig();
		return isset($config['exclude_from_page_types']) ? $config['exclude_from_page_types'] : array();
	}

	/*
	 * Usage of extra css classes configurable from yaml
	 */
	public function getUseExtraCSSClasses(){
		$config = $this->getThemeConfig();
		return isset($config['use_extra_css_classes']) ? $config['use_extra_css_classes'] : false;
	}
	

	/**
	 * Sets fields to be displayed in grid.
	 * Standart set of fields, and can be extended/modified using the extension hook {@see updateGridDisplayFields}
	 * Params set on {@link GridFieldConfig_BlockManager}, but in order to be extendable, needs to be moved here
	 * @param string $currentPageClassName name of the current page, to filter block areas
	 * @param boolean $editable fields to return are editable or not
	 * @param boolean $aboveOrBelow only applicable if fields editable
	 * @return array fields to be displayed on the grid
	 *
	 * @see GridFieldConfig_BlockManager
	 */
	public function getGridDisplayFields($currentPageClassName = null, $editable = false, $aboveOrBelow = false) {
		if($currentPageClassName !== null) {
			$areasFieldSource = $this->getAreasForPageType($currentPageClassName);
		} else {
			$areasFieldSource = $this->getAreasForTheme();
		}
		
		$displayfields = array(
			'singular_name' => 'Block Type',
			'Title' => 'Title',
			'BlockArea' => 'Block Area',
			'isPublishedNice' => 'Published',
			'UsageListAsString' => 'Used on'
		);
		
		if($editable) {
			$displayfields = array(
				'singular_name' => array('title' => 'Block Type', 'field' => 'ReadonlyField'),
				'Title'        	=> array('title' => 'Title', 'field' => 'ReadonlyField'),
				'BlockArea'	=> array(	
					'title' => 'Block Area
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
						// the &nbsp;s prevent wrapping of dropdowns
					'callback' => function() use ($areasFieldSource){
							return DropdownField::create('BlockArea', 'Block Area', $areasFieldSource)
								->setHasEmptyDefault(true);
						}
				),
				'isPublishedNice'	=> array('title' => 'Published', 'field' => 'ReadonlyField'),
				'UsageListAsString' => array('title' => 'Used on', 'field' => 'ReadonlyField'),
			);
			
			if($aboveOrBelow){
				$displayfields['AboveOrBelow'] = array(
					'title' => 'Above or Below',
					'callback' => function() {
						return DropdownField::create('AboveOrBelow', 'Above or Below', BlockSet::config()->get('above_or_below_options'));
					}
				);
			}
		}
		
		$this->extend('updateGridDisplayFields', $displayfields);
		
		return $displayfields;
	}

}