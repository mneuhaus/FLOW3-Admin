<?php
 
namespace F3\Admin\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
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

/**
 * @api
 * @scope prototype
 */
class ResourcesViewHelper extends \F3\Fluid\Core\ViewHelper\AbstractViewHelper {
	/**
	 * @var \F3\FLOW3\Resource\Publishing\ResourcePublisher
	 */
	protected $resourcePublisher;

	/**
	 * Inject the FLOW3 resource publisher.
	 *
	 * @param \F3\FLOW3\Resource\Publishing\ResourcePublisher $resourcePublisher
	 */
	public function injectResourcePublisher(\F3\FLOW3\Resource\Publishing\ResourcePublisher $resourcePublisher) {
		$this->resourcePublisher = $resourcePublisher;
	}

    protected $templates = array(
        "js"    => '<script src="@file" type="text/javascript" charset="utf-8"></script>\n',
        "css"   => '<link rel="stylesheet" href="@file" type="text/css" media="screen" charset="utf-8">\n'
    );

    static protected $files = array();
	
	/**
	 *
	 * @param string $file
     * @param string $dependencies
	 * @return string
	 */
	public function _render($file = null, $dependencies = null) {
		$package = $this->controllerContext->getRequest()->getControllerPackageKey();
		$mirrorPath = str_replace("/http://dev.flow3.local/_","",$this->resourcePublisher->getStaticResourcesWebBaseUri());
		$uri = $mirrorPath . '/Packages/' . $package . '/';

        if($file == null && $dependencies == null){
            
        }else{
            
        }

        return "";
	}

    public function render(){
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
}

?>
