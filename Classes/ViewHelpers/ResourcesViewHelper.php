<?php
 
namespace Admin\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Doctrine\ORM\Mapping as ORM;
use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * @api
 * @FLOW3\Scope("prototype")
 */
class ResourcesViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {
	/**
	 * @var \TYPO3\FLOW3\Resource\Publishing\ResourcePublisher
	 */
	protected $resourcePublisher;

	/**
	 * Inject the FLOW3 resource publisher.
	 *
	 * @param \TYPO3\FLOW3\Resource\Publishing\ResourcePublisher $resourcePublisher
	 */
	public function injectResourcePublisher(\TYPO3\FLOW3\Resource\Publishing\ResourcePublisher $resourcePublisher) {
		$this->resourcePublisher = $resourcePublisher;
	}

	protected $templates = array(
		"js"	=> '<script src="@file" type="text/javascript" charset="utf-8"></script>',
		"css"   => '<link rel="stylesheet" href="@file" type="text/css" media="screen" charset="utf-8">'
	);

	static protected $files = array(
			"js/jquery/jquery.js",
			"js/jquery/jquery.ui.js",
			"js/jquery/jquery.livesearch.js",
			"js/jquery/jquery.hotkeys.js",
			"js/jquery/jquery.selectbox.js",
			"js/jquery/jquery.elastic.js",
			"js/jquery/jquery.keynav.js",
			"js/main.js",
		
			"css/jquery/jquery.ui.css",
			"css/jquery/jquery.selector.css"
	);
	
	/**
	 *
	 * @param string $file
	 * @param string $dependencies
	 * @return string
	 */
	public function render($file = null, $dependencies = null) {
		$package = $this->controllerContext->getRequest()->getControllerPackageKey();
		$mirrorPath = str_replace("/http://dev.flow3.local/_","",$this->resourcePublisher->getStaticResourcesWebBaseUri());
		$uri = $mirrorPath . '/Packages/' . $package . '/';

		if($file == null && $dependencies == null){
			$output = "";
			foreach(self::$files as $key => $file){
				$parts = explode(".",$file);
				$extension = array_pop($parts);
				if(array_key_exists($extension, $this->templates)){
					$output.= str_replace("@file",$uri.$file,$this->templates[$extension]);
				}
				unset(self::$files[$key]);
			}
			return $output;
		}else{
			$filename = basename($file);
			if($dependencies == null){
				self::$files[]= $file;
			}
		}

		return "";
	}

	public function _render(){
		$package = $this->controllerContext->getRequest()->getControllerPackageKey();
		$mirrorPath = str_replace("/http://dev.flow3.local/_","",$this->resourcePublisher->getStaticResourcesWebBaseUri());
		$uri = $mirrorPath . '/Packages/' . $package . '/';
		$output = "";
		$jsfiles = array(
			"js/jquery/jquery.js",
			"js/jquery/jquery.ui.js",
			"js/jquery/jquery.livesearch.js",
			"js/jquery/jquery.hotkeys.js",
			"js/jquery/jquery.selectbox.js",
			"js/jquery/jquery.elastic.js",
			"js/jquery/jquery.keynav.js",
			"js/main.js"
		);
		foreach($jsfiles as $jsfile){
			$output .= '<script src="'.$uri.$jsfile.'" type="text/javascript" charset="utf-8"></script>'."\n";
		}
		$cssfiles = array(
			"css/jquery/jquery.ui.css",
			"css/jquery/jquery.selector.css"
		);
		foreach($cssfiles as $cssfile){
			$output .= '<link rel="stylesheet" href="'.$uri.$cssfile.'" type="text/css" media="screen" title="no title" charset="utf-8">'."\n";
		}
		return $output;
	}

	public function array_insert($array, $insertion, $position = null){
		if($position == null)
			$position = count($position);
		
		$before = array_slice($array,0,$position);
		$after = array_slice($array,$position);

		$array = $before;
		$array[]= $insertion;
		foreach($after as $element)
			$array[]= $element;
		
		return $array;
	}
}

?>
