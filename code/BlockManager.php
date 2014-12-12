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


	private function getTheme(){
		if(class_exists('Multisites')){
			return Multisites::inst()->getActiveSite()->Theme;
		}else{
			return Config::inst()->get('SSViewer', 'theme');
		}
	}
	
	/*
	 * Usage of GlobalBlocks configurable from yaml
	 */
	public function getUseGlobalBlocks(){
		//return false;
		$theme = $this->getTheme();
		if(!$theme){ return true; }
		$config = $this->config()->get('themes');
		
		if(!isset($config[$theme]['use_global_blocks'])){
			return true;
		}
		return $config[$theme]['use_global_blocks'];
	}
	
	/*
	 * Usage of BlockSets configurable from yaml
	 */
	public function getUseBlockSets(){
		//return false;
		$theme = $this->getTheme();
		if(!$theme){ return true; }
		$config = $this->config()->get('themes');
		
		if(!isset($config[$theme]['use_blocksets'])){
			return true;
		}
		return $config[$theme]['use_blocksets'];
	}

}