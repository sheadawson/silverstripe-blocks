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

		return $keyAsValue ? ArrayLib::valuekey(array_keys($areas)) : $areas;
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
				if (($except == $class) || (is_array($except) && in_array($class, $config['except']))) {
					unset($areas[$area]);
					continue;
				}
			}

			if(isset($config['only'])) {
				$only = $config['only'];
				if (($only != $class) || (is_array($only) && !in_array($class, $config['only']))) {
					unset($areas[$area]);
					continue;
				}
			}
		}

		return ArrayLib::valuekey(array_keys($areas));
	}


	private function getTheme(){
		if(class_exists('Multisites')){
			return Multisites::inst()->getActiveSite()->Theme;
		}else{
			return Config::inst()->get('SSViewer', 'theme');
		}
	}

}