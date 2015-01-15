<?php
/**
 * @package silverstipe blocks
 * @author Shea Dawson <shea@silverstripe.com.au>
 */
class BlockManager extends Object{

	private static $themes = array();

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
		$areas 	= $this->getAreasForTheme(null, false);

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


	/*
	 * Get the current/active theme
	 */
	private function getTheme(){
		return Config::inst()->get('SSViewer', 'theme');
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

}