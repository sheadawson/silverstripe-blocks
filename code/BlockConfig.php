<?php
/**
 * @package silverstipe blocks
 * @author Shea Dawson <shea@silverstripe.com.au>
 */
class BlockConfig extends Object{

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
		$theme 	= $theme ? $theme : Config::inst()->get('SSViewer', 'theme');
		$config = $this->config()->get('themes');
		$areas 	= $config[$theme]['areas'];

		return $keyAsValue ? ArrayLib::valuekey(array_keys($areas)) : $areas;
	}


	/**
	 * Gets an array of all areas defined for the current theme that are compatible
	 * with pages of type $class
	 * @param string $class
	 * @return array $areas
	 **/
	public function getAreasForPageClass($class = null){
		$class 	= $class ? $class : Controller::curr()->currentPage()->ClassName;
		$areas 	= $this->getAreasForTheme(null, false);

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

}