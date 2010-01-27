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
	
	/**
	 *
	 *
	 * @return string
	 */
	public function render() {
		$package = $this->controllerContext->getRequest()->getControllerPackageKey();
		$mirrorPath = str_replace("/http://dev.flow3.local/_","",$this->resourcePublisher->getStaticResourcesWebBaseUri());
		$uri = $mirrorPath . '/Packages/' . $package . '/';
		// if ($absolute) {
		// 	$uri = $this->controllerContext->getRequest()->getBaseURI() . $uri;
		// }
		
		$output = "";
		$jsfiles = array(
			"js/jquery-1.3.2.min.js",
			"js/jquery-ui-1.7.2.custom.min.js",
			"js/daterangepicker.jQuery.js",
			"js/jquery-ui.spinner.js",
			"js/jquery.scrollTo-min.js",
			"js/jquery.localisation-min.js",
			"js/ui.multiselect.js",
			"js/ui-multiselect-en.js",
			"js/jquery.autoSuggest.js",
			"js/jquery.MultiFile.js",
			"js/jquery.elastic.js",
			"js/jquery.hotkeys.js",
			"js/jquery.keynav.js",
			"js/jquery.datejs.js",
			"js/ckeditor/ckeditor.js",
			"js/ckeditor/adapters/jquery.js",
			"js/markitup/jquery.markitup.js",
			"js/jquery.sexy-combo.js",
			"js/markitup/sets/wiki/set.js",
			"js/markitup/sets/texy/set.js",
			"js/markitup/sets/textile/set.js",
			"js/markitup/sets/markdown/set.js",
			"js/markitup/sets/dotclear/set.js",
			"js/markitup/sets/bbcode/set.js",
			"js/main.js"
		);
		foreach($jsfiles as $jsfile){
			$output .= '<script src="'.$uri.$jsfile.'" type="text/javascript" charset="utf-8"></script>'."\n";
		}
		$cssfiles = array(
			"js/markitup/sets/wiki/style.css",
			"js/markitup/sets/texy/style.css",
			"js/markitup/sets/textile/style.css",
			"js/markitup/sets/markdown/style.css",
			"js/markitup/sets/dotclear/style.css",
			"js/markitup/sets/bbcode/style.css"
		);
		foreach($cssfiles as $cssfile){
			$output .= '<link rel="stylesheet" href="'.$uri.$cssfile.'" type="text/css" media="screen" title="no title" charset="utf-8">'."\n";
		}
		return $output;
	}
}

?>
